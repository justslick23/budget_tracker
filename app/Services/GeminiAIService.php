<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeminiAIService
{
    private $apiKey;
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private $model = 'gemini-2.0-flash-exp';
    private $lastRequestTime = null;
    private $minRequestInterval = 4; // 15 requests per minute = 4 seconds between requests
    private $maxRetries = 3;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        
        if (empty($this->apiKey)) {
            Log::error('GEMINI_API_KEY is not set in environment');
        } else {
            Log::info('GeminiAIService initialized', [
                'model' => $this->model,
                'api_key_length' => strlen($this->apiKey)
            ]);
        }
    }

    public function analyzeBudgetData($data)
    {
        $startTime = microtime(true);
        $requestId = uniqid('gemini_', true);
        
        Log::info('Starting budget analysis', [
            'request_id' => $requestId,
            'data_keys' => array_keys($data),
            'total_income' => $data['totalIncome'] ?? 0,
            'total_expenses' => $data['totalExpenses'] ?? 0,
            'transaction_count' => count($data['allTransactions'] ?? [])
        ]);

        if (empty($this->apiKey)) {
            Log::error('Analysis aborted: API key missing', ['request_id' => $requestId]);
            return $this->getFallbackResponse();
        }

        // Create cache key based on data hash
        $cacheKey = 'gemini_analysis_' . md5(json_encode($data));
        
        // Check cache first
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            Log::info('Returning cached analysis', [
                'request_id' => $requestId,
                'cache_key' => $cacheKey,
                'cache_age_minutes' => Cache::get($cacheKey . '_timestamp') ? 
                    round((time() - Cache::get($cacheKey . '_timestamp')) / 60, 2) : 'unknown'
            ]);
            return $cached;
        }

        // Check rate limit
        $rateLimitKey = 'gemini_rate_limit_' . date('YmdHi');
        $requestCount = Cache::get($rateLimitKey, 0);
        
        if ($requestCount >= 15) {
            Log::warning('Rate limit exceeded', [
                'request_id' => $requestId,
                'current_count' => $requestCount,
                'limit' => 15,
                'period' => 'per_minute'
            ]);
            return $this->getFallbackResponse();
        }

        try {
            // Throttle request to prevent rate limiting
            $this->throttleRequest($requestId);
            
            // Increment rate limit counter
            Cache::put($rateLimitKey, $requestCount + 1, now()->addMinute());
            Log::info('Rate limit check passed', [
                'request_id' => $requestId,
                'current_count' => $requestCount + 1,
                'limit' => 15
            ]);

            $prompt = $this->buildEnhancedAnalysisPrompt($data);
            $promptLength = strlen($prompt);
            $estimatedTokens = $promptLength / 4; // Rough estimate
            
            Log::info('Prompt generated', [
                'request_id' => $requestId,
                'prompt_length_chars' => $promptLength,
                'estimated_tokens' => round($estimatedTokens),
                'prompt_preview' => substr($prompt, 0, 200) . '...'
            ]);
            
            $url = $this->baseUrl . $this->model . ':generateContent?key=' . $this->apiKey;
            
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 8192,
                    'responseMimeType' => 'application/json'
                ]
            ];

            Log::info('Sending request to Gemini API', [
                'request_id' => $requestId,
                'url' => $this->baseUrl . $this->model . ':generateContent',
                'model' => $this->model,
                'config' => $payload['generationConfig']
            ]);

            $response = $this->callGeminiAPIWithRetry($url, $payload, $requestId);

            if ($response && $response->successful()) {
                $responseTime = round((microtime(true) - $startTime) * 1000, 2);
                $result = $response->json();
                
                Log::info('Gemini API response received', [
                    'request_id' => $requestId,
                    'status' => $response->status(),
                    'response_time_ms' => $responseTime,
                    'response_size_bytes' => strlen($response->body()),
                    'has_candidates' => isset($result['candidates'])
                ]);

                $parsed = $this->parseAIResponse($result, $requestId);
                
                // Cache the successful result
                Cache::put($cacheKey, $parsed, now()->addHours(1));
                Cache::put($cacheKey . '_timestamp', time(), now()->addHours(1));
                
                Log::info('Analysis completed successfully', [
                    'request_id' => $requestId,
                    'total_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    'cached' => true,
                    'recommendations_count' => count($parsed['recommendations'] ?? []),
                    'insights_count' => count($parsed['insights'] ?? [])
                ]);
                
                return $parsed;
            }

            // Handle specific error codes
            $statusCode = $response ? $response->status() : 0;
            $responseBody = $response ? $response->body() : 'No response';
            
            Log::error('Gemini API request failed', [
                'request_id' => $requestId,
                'status_code' => $statusCode,
                'response_body' => substr($responseBody, 0, 500),
                'total_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);
            
            return $this->getFallbackResponse();

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection error to Gemini API', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'total_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);
            return $this->getFallbackResponse();
            
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Request exception from Gemini API', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'status' => $e->response ? $e->response->status() : 'unknown',
                'total_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);
            return $this->getFallbackResponse();
            
        } catch (\Exception $e) {
            Log::error('Unexpected exception in Gemini API service', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'total_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);
            return $this->getFallbackResponse();
        }
    }

    private function throttleRequest($requestId)
    {
        if ($this->lastRequestTime !== null) {
            $elapsed = microtime(true) - $this->lastRequestTime;
            if ($elapsed < $this->minRequestInterval) {
                $sleepTime = $this->minRequestInterval - $elapsed;
                Log::info('Throttling request', [
                    'request_id' => $requestId,
                    'elapsed_seconds' => round($elapsed, 2),
                    'sleep_seconds' => round($sleepTime, 2),
                    'min_interval' => $this->minRequestInterval
                ]);
                usleep($sleepTime * 1000000);
            }
        }
        $this->lastRequestTime = microtime(true);
    }

    private function callGeminiAPIWithRetry($url, $payload, $requestId)
    {
        $attempt = 0;
        
        while ($attempt < $this->maxRetries) {
            $attempt++;
            $attemptStartTime = microtime(true);
            
            try {
                Log::info('API call attempt', [
                    'request_id' => $requestId,
                    'attempt' => $attempt,
                    'max_retries' => $this->maxRetries
                ]);

                $response = Http::timeout(60)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-Request-ID' => $requestId
                    ])
                    ->post($url, $payload);
                
                $attemptTime = round((microtime(true) - $attemptStartTime) * 1000, 2);
                
                if ($response->successful()) {
                    Log::info('API call successful', [
                        'request_id' => $requestId,
                        'attempt' => $attempt,
                        'response_time_ms' => $attemptTime,
                        'status' => $response->status()
                    ]);
                    return $response;
                }
                
                // Handle 429 Rate Limit
                if ($response->status() === 429) {
                    if ($attempt < $this->maxRetries) {
                        $waitTime = pow(2, $attempt); // Exponential backoff: 2, 4, 8 seconds
                        Log::warning('Rate limit hit, retrying', [
                            'request_id' => $requestId,
                            'attempt' => $attempt,
                            'status' => 429,
                            'wait_seconds' => $waitTime,
                            'next_attempt' => $attempt + 1
                        ]);
                        sleep($waitTime);
                        continue;
                    } else {
                        Log::error('Rate limit exceeded, max retries reached', [
                            'request_id' => $requestId,
                            'attempts' => $attempt,
                            'status' => 429
                        ]);
                    }
                }
                
                // Other error codes
                Log::warning('API call failed', [
                    'request_id' => $requestId,
                    'attempt' => $attempt,
                    'status' => $response->status(),
                    'response_preview' => substr($response->body(), 0, 200)
                ]);
                
                return $response;
                
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('Connection failed on attempt', [
                    'request_id' => $requestId,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'attempt_time_ms' => round((microtime(true) - $attemptStartTime) * 1000, 2)
                ]);
                
                if ($attempt < $this->maxRetries) {
                    $waitTime = pow(2, $attempt);
                    Log::info('Retrying after connection error', [
                        'request_id' => $requestId,
                        'wait_seconds' => $waitTime
                    ]);
                    sleep($waitTime);
                    continue;
                }
                throw $e;
                
            } catch (\Exception $e) {
                Log::error('Unexpected error on attempt', [
                    'request_id' => $requestId,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e)
                ]);
                
                if ($attempt < $this->maxRetries) {
                    sleep(pow(2, $attempt));
                    continue;
                }
                throw $e;
            }
        }
        
        Log::error('All retry attempts exhausted', [
            'request_id' => $requestId,
            'total_attempts' => $attempt
        ]);
        
        return null;
    }

    private function buildEnhancedAnalysisPrompt(array $data)
    {
        $prompt = "You are an expert financial advisor. Analyze this financial data and provide actionable insights in JSON format.\n\n";
        
        // BASIC SUMMARY
        $prompt .= "=== CURRENT MONTH SUMMARY ===\n";
        $prompt .= "Income: M" . number_format($data['totalIncome'] ?? 0, 2) . "\n";
        $prompt .= "Expenses: M" . number_format($data['totalExpenses'] ?? 0, 2) . "\n";
        $prompt .= "Budget: M" . number_format($data['monthlyBudget'] ?? 0, 2) . "\n";
        $prompt .= "Remaining: M" . number_format($data['remainingBudget'] ?? 0, 2) . "\n";
        $prompt .= "Days Elapsed: " . ($data['daysElapsed'] ?? 0) . " / Days Remaining: " . ($data['daysRemaining'] ?? 0) . "\n";
        $prompt .= "Net Savings: M" . number_format(($data['totalIncome'] ?? 0) - ($data['totalExpenses'] ?? 0), 2) . "\n\n";

        // INDIVIDUAL TRANSACTIONS
        if (!empty($data['allTransactions']) && is_array($data['allTransactions'])) {
            $prompt .= "=== ALL INDIVIDUAL TRANSACTIONS ===\n";
            $prompt .= "Total Transactions: " . count($data['allTransactions']) . "\n\n";
            
            // Group and analyze transactions
            $transactionsByItem = [];
            $transactionsByCategory = [];
            
            foreach ($data['allTransactions'] as $txn) {
                $desc = strtolower(trim($txn['description'] ?? 'unknown'));
                $cat = $txn['category'] ?? 'other';
                $amt = floatval($txn['amount'] ?? 0);
                
                // Group by description
                if (!isset($transactionsByItem[$desc])) {
                    $transactionsByItem[$desc] = [
                        'description' => $desc,
                        'category' => $cat,
                        'count' => 0,
                        'total' => 0,
                        'amounts' => []
                    ];
                }
                $transactionsByItem[$desc]['count']++;
                $transactionsByItem[$desc]['total'] += $amt;
                $transactionsByItem[$desc]['amounts'][] = $amt;
                
                // Group by category
                if (!isset($transactionsByCategory[$cat])) {
                    $transactionsByCategory[$cat] = [
                        'total' => 0,
                        'count' => 0,
                        'items' => []
                    ];
                }
                $transactionsByCategory[$cat]['total'] += $amt;
                $transactionsByCategory[$cat]['count']++;
                $transactionsByCategory[$cat]['items'][] = $desc;
            }
            
            // Sort by total spending
            uasort($transactionsByItem, fn($a, $b) => $b['total'] <=> $a['total']);
            
            // Show top 20 spending items
            $prompt .= "TOP SPENDING ITEMS:\n";
            $count = 0;
            foreach ($transactionsByItem as $item) {
                if ($count++ >= 20) break;
                $avgAmount = $item['total'] / $item['count'];
                $prompt .= sprintf(
                    "- %s: M%.2f (x%d times, avg M%.2f, category: %s)\n",
                    ucfirst($item['description']),
                    $item['total'],
                    $item['count'],
                    $avgAmount,
                    $item['category']
                );
            }
            $prompt .= "\n";
            
            // Show recurring patterns
            $prompt .= "RECURRING EXPENSES (3+ occurrences):\n";
            foreach ($transactionsByItem as $item) {
                if ($item['count'] >= 3) {
                    $prompt .= sprintf(
                        "- %s: %d times (M%.2f total)\n",
                        ucfirst($item['description']),
                        $item['count'],
                        $item['total']
                    );
                }
            }
            $prompt .= "\n";
            
            // Category breakdown
            $prompt .= "SPENDING BY CATEGORY:\n";
            arsort($transactionsByCategory);
            foreach ($transactionsByCategory as $cat => $info) {
                $prompt .= sprintf(
                    "- %s: M%.2f (%d transactions)\n",
                    ucfirst($cat),
                    $info['total'],
                    $info['count']
                );
            }
            $prompt .= "\n";
        }

        // HISTORICAL TRENDS
        if (!empty($data['historicalTrends'])) {
            $prompt .= "=== HISTORICAL TRENDS ===\n";
            $prompt .= "Moving Average: M" . number_format($data['historicalTrends']['moving_average'] ?? 0, 2) . "\n";
            $prompt .= "Trend: " . ($data['historicalTrends']['trend_direction'] ?? 'stable') . "\n";
            $prompt .= "Projected Month-End Total: M" . number_format($data['historicalTrends']['projected_total'] ?? 0, 2) . "\n\n";
        }

        // SPENDING PATTERNS
        if (!empty($data['spendingPatterns'])) {
            $prompt .= "=== SPENDING PATTERNS ===\n";
            if (isset($data['spendingPatterns']['weekday_vs_weekend'])) {
                $wvw = $data['spendingPatterns']['weekday_vs_weekend'];
                $prompt .= "Weekday Spending: M" . number_format($wvw['weekday'] ?? 0, 2) . "\n";
                $prompt .= "Weekend Spending: M" . number_format($wvw['weekend'] ?? 0, 2) . "\n";
            }
            if (isset($data['spendingPatterns']['time_of_month'])) {
                $tom = $data['spendingPatterns']['time_of_month'];
                $prompt .= "Beginning of Month: M" . number_format($tom['beginning'] ?? 0, 2) . "\n";
                $prompt .= "Middle of Month: M" . number_format($tom['middle'] ?? 0, 2) . "\n";
                $prompt .= "End of Month: M" . number_format($tom['end'] ?? 0, 2) . "\n";
            }
            $prompt .= "\n";
        }

        // UNUSUAL TRANSACTIONS
        if (!empty($data['unusualTransactions'])) {
            $prompt .= "=== UNUSUAL SPENDING DETECTED ===\n";
            foreach ($data['unusualTransactions'] as $unusual) {
                $prompt .= sprintf(
                    "- %s: M%.2f (%s) - %.0f%% above average\n",
                    $unusual['description'] ?? 'Unknown',
                    $unusual['amount'] ?? 0,
                    $unusual['category'] ?? 'Unknown',
                    $unusual['deviation'] ?? 0
                );
            }
            $prompt .= "\n";
        }

        // CATEGORY DATA
        if (!empty($data['categoryData'])) {
            $prompt .= "=== CATEGORY PERFORMANCE ===\n";
            foreach ($data['categoryData'] as $cat) {
                if ($cat['expense'] > 0) {
                    $prompt .= sprintf(
                        "- %s: M%.2f spent, M%.2f budgeted, M%.2f average (%+.1f%% vs avg)\n",
                        $cat['name'],
                        $cat['expense'],
                        $cat['budget'],
                        $cat['average_amount'],
                        $cat['vs_average']
                    );
                }
            }
            $prompt .= "\n";
        }

        // REQUEST SPECIFIC FORMAT
        $prompt .= "=== REQUIRED JSON OUTPUT FORMAT ===\n";
        $prompt .= "Based on ALL the transaction-level data above, provide detailed analysis in this EXACT JSON structure:\n\n";
        $prompt .= <<<'JSON'
{
  "predictions": {
    "monthEndTotal": <number>,
    "budgetStatus": "within_budget|close_to_budget|over_budget",
    "expectedVariance": <number>,
    "confidence": <number 0-100>
  },
  "recommendations": [
    {
      "title": "Brief title",
      "description": "Detailed explanation",
      "priority": "high|medium|low",
      "category": "food|transport|entertainment|utilities|general",
      "specificItems": ["Specific item 1 from transactions", "Specific item 2", "..."],
      "potentialSavings": <number>,
      "implementationDifficulty": "easy|moderate|hard"
    }
  ],
  "insights": [
    "Key insight 1 with specific numbers",
    "Key insight 2 mentioning specific items",
    "..."
  ],
  "spendingPatternInsights": {
    "topSpendingItems": [
      {
        "item": "Exact item name from transactions",
        "total": <number>,
        "percentage": <number>
      }
    ],
    "recurringExpenses": [
      {
        "item": "Exact recurring item",
        "frequency": "daily|weekly|monthly",
        "amount": <number>
      }
    ],
    "unusualSpikes": [
      {
        "item": "Exact item name",
        "amount": <number>,
        "reason": "Why this is unusual"
      }
    ],
    "costPerDay": <number>
  },
  "behavioralInsights": {
    "spendingTriggers": ["trigger 1", "trigger 2"],
    "improvementOpportunities": ["opportunity 1", "opportunity 2"],
    "strengths": ["strength 1", "strength 2"]
  },
  "nextMonthForecast": {
    "expectedSpending": <number>,
    "confidence": <number>,
    "reasoning": "Brief explanation",
    "riskFactors": ["risk 1", "risk 2"]
  }
}
JSON;

        $prompt .= "\n\nIMPORTANT RULES:\n";
        $prompt .= "1. Use EXACT item names from the transactions (e.g., 'taxi', 'chicken', 'data bundle')\n";
        $prompt .= "2. In 'specificItems' arrays, reference ACTUAL transactions from the data\n";
        $prompt .= "3. Be specific - don't say 'reduce food spending', say 'reduce taxi usage' or 'cut back on KFC purchases'\n";
        $prompt .= "4. Calculate percentages based on actual transaction data\n";
        $prompt .= "5. Identify patterns in recurring items\n";
        $prompt .= "6. Highlight the most expensive individual transactions\n";
        $prompt .= "7. Return ONLY valid JSON, no markdown formatting\n";

        return $prompt;
    }

    private function parseAIResponse($result, $requestId)
    {
        try {
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $text = $result['candidates'][0]['content']['parts'][0]['text'];
                $originalLength = strlen($text);
                
                Log::info('Parsing AI response', [
                    'request_id' => $requestId,
                    'response_length' => $originalLength
                ]);
                
                // Clean up potential markdown formatting
                $text = preg_replace('/```json\s*/i', '', $text);
                $text = preg_replace('/```\s*$/i', '', $text);
                $text = trim($text);
                
                $decoded = json_decode($text, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    Log::info('AI response parsed successfully', [
                        'request_id' => $requestId,
                        'has_predictions' => isset($decoded['predictions']),
                        'recommendations_count' => count($decoded['recommendations'] ?? []),
                        'insights_count' => count($decoded['insights'] ?? [])
                    ]);
                    
                    // Ensure all required keys exist
                    return [
                        'predictions' => $decoded['predictions'] ?? [],
                        'recommendations' => $decoded['recommendations'] ?? [],
                        'insights' => $decoded['insights'] ?? [],
                        'spendingPatternInsights' => $decoded['spendingPatternInsights'] ?? [],
                        'behavioralInsights' => $decoded['behavioralInsights'] ?? [],
                        'nextMonthForecast' => $decoded['nextMonthForecast'] ?? []
                    ];
                }
                
                Log::error('Failed to decode AI response JSON', [
                    'request_id' => $requestId,
                    'json_error' => json_last_error_msg(),
                    'json_error_code' => json_last_error(),
                    'text_length' => strlen($text),
                    'text_preview' => substr($text, 0, 500)
                ]);
            } else {
                Log::error('AI response missing expected structure', [
                    'request_id' => $requestId,
                    'result_keys' => array_keys($result),
                    'has_candidates' => isset($result['candidates'])
                ]);
            }
            
            return $this->getFallbackResponse();
            
        } catch (\Exception $e) {
            Log::error('Exception while parsing AI response', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'line' => $e->getLine()
            ]);
            return $this->getFallbackResponse();
        }
    }

    private function getFallbackResponse()
    {
        Log::info('Returning fallback response');
        
        return [
            'predictions' => [
                'monthEndTotal' => 0,
                'budgetStatus' => 'unknown',
                'expectedVariance' => 0,
                'confidence' => 0
            ],
            'recommendations' => [
                [
                    'title' => 'AI Analysis Unavailable',
                    'description' => 'Unable to generate AI insights at this time. Please check your API configuration or try again later.',
                    'priority' => 'low',
                    'category' => 'general',
                    'specificItems' => [],
                    'potentialSavings' => 0,
                    'implementationDifficulty' => 'easy'
                ]
            ],
            'insights' => [
                'AI analysis is temporarily unavailable. Please check logs for details.'
            ],
            'spendingPatternInsights' => [
                'topSpendingItems' => [],
                'recurringExpenses' => [],
                'unusualSpikes' => [],
                'costPerDay' => 0
            ],
            'behavioralInsights' => [
                'spendingTriggers' => [],
                'improvementOpportunities' => [],
                'strengths' => []
            ],
            'nextMonthForecast' => [
                'expectedSpending' => 0,
                'confidence' => 0,
                'reasoning' => 'Not available',
                'riskFactors' => []
            ]
        ];
    }

    /**
     * Get current rate limit status
     */
    public function getRateLimitStatus()
    {
        $rateLimitKey = 'gemini_rate_limit_' . date('YmdHi');
        $requestCount = Cache::get($rateLimitKey, 0);
        
        $status = [
            'current_count' => $requestCount,
            'limit' => 15,
            'remaining' => max(0, 15 - $requestCount),
            'resets_at' => date('Y-m-d H:i:s', strtotime('+1 minute', strtotime(date('Y-m-d H:i:00'))))
        ];
        
        Log::info('Rate limit status checked', $status);
        
        return $status;
    }

    /**
     * Clear cache for a specific analysis
     */
    public function clearCache($data)
    {
        $cacheKey = 'gemini_analysis_' . md5(json_encode($data));
        $cleared = Cache::forget($cacheKey);
        Cache::forget($cacheKey . '_timestamp');
        
        Log::info('Cache cleared', [
            'cache_key' => $cacheKey,
            'success' => $cleared
        ]);
        
        return $cleared;
    }

    /**
     * Clear all rate limits (useful for testing)
     */
    public function clearRateLimits()
    {
        $keys = Cache::get('gemini_rate_limit_keys', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        Log::warning('All rate limits cleared');
        
        return true;
    }
}