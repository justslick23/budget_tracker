<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAIService
{
    private $apiKey;
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private $model = 'gemini-2.0-flash-exp';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        
        if (empty($this->apiKey)) {
            Log::error('GEMINI_API_KEY is not set');
        }
    }

    public function analyzeBudgetData($data)
    {
        if (empty($this->apiKey)) {
            return $this->getFallbackResponse();
        }

        try {
            $prompt = $this->buildEnhancedAnalysisPrompt($data);
            
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

            $response = Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->successful()) {
                $result = $response->json();
                return $this->parseAIResponse($result);
            }

            Log::error('Gemini API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return $this->getFallbackResponse();

        } catch (\Exception $e) {
            Log::error('Gemini API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->getFallbackResponse();
        }
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

        // INDIVIDUAL TRANSACTIONS - THIS WAS MISSING!
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
                $date = $txn['date'] ?? '';
                
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

    private function parseAIResponse($result)
    {
        try {
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $text = $result['candidates'][0]['content']['parts'][0]['text'];
                
                // Clean up potential markdown formatting
                $text = preg_replace('/```json\s*/i', '', $text);
                $text = preg_replace('/```\s*$/i', '', $text);
                $text = trim($text);
                
                $decoded = json_decode($text, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
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
                    'error' => json_last_error_msg(),
                    'text_preview' => substr($text, 0, 500)
                ]);
            }
            
            return $this->getFallbackResponse();
            
        } catch (\Exception $e) {
            Log::error('Error parsing AI response', [
                'message' => $e->getMessage()
            ]);
            return $this->getFallbackResponse();
        }
    }

    private function getFallbackResponse()
    {
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
                    'description' => 'Unable to generate AI insights at this time. Please check your API configuration.',
                    'priority' => 'low',
                    'category' => 'general',
                    'specificItems' => [],
                    'potentialSavings' => 0,
                    'implementationDifficulty' => 'easy'
                ]
            ],
            'insights' => [
                'AI analysis is temporarily unavailable'
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
}