<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GeminiAIService
{
    private $apiKey;
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private $model = 'gemini-3-pro';
    private $maxRetries = 3;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        
        if (empty($this->apiKey)) {
            Log::error('GEMINI_API_KEY is not set in environment');
        }
    }

    public function analyzeFinancialData($data)
    {
        $startTime = microtime(true);
        $requestId = uniqid('gemini_', true);
        
        Log::info('Starting AI financial analysis', [
            'request_id' => $requestId,
            'transactions' => count($data['transactions'] ?? []),
            'historical_months' => count($data['historical_months'] ?? [])
        ]);

        if (empty($this->apiKey)) {
            return $this->getFallbackResponse();
        }

        $cacheKey = 'gemini_analysis_' . md5(json_encode([
            'transactions' => count($data['transactions'] ?? []),
            'total_expenses' => $data['total_expenses'] ?? 0,
            'total_income' => $data['total_income'] ?? 0,
            'month' => $data['period_start'] ?? '',
            'categories' => count($data['category_budgets'] ?? [])
        ]));
        
        if (Cache::has($cacheKey)) {
            Log::info('Returning cached AI analysis', ['request_id' => $requestId]);
            return Cache::get($cacheKey);
        }

        if (!$this->checkRateLimit($requestId)) {
            return $this->getFallbackResponse();
        }

        try {
            $prompt = $this->buildEnhancedPrompt($data);
            
            $response = $this->callGeminiAPI($prompt, $requestId);

            if ($response && $response->successful()) {
                $result = $response->json();
                $parsed = $this->parseAIResponse($result, $requestId);
                
                Cache::put($cacheKey, $parsed, now()->addMinutes(30));
                
                Log::info('AI analysis completed', [
                    'request_id' => $requestId,
                    'time_ms' => round((microtime(true) - $startTime) * 1000, 2)
                ]);
                
                return $parsed;
            }

            Log::error('Gemini API request failed', ['request_id' => $requestId]);
            return $this->getFallbackResponse();

        } catch (\Exception $e) {
            Log::error('AI analysis exception', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->getFallbackResponse();
        }
    }

    private function buildEnhancedPrompt(array $data)
    {
        $prompt = "You are a thoughtful, realistic financial advisor AI who understands that humans aren't robots. Analyze this financial data with empathy and practicality.\n\n";
        
        // ========== CURRENT PERIOD OVERVIEW ==========
        $prompt .= "=== CURRENT PERIOD ANALYSIS ===\n";
        $prompt .= sprintf("Period: %s to %s\n", $data['period_start'] ?? 'Unknown', $data['period_end'] ?? 'Unknown');
        $prompt .= sprintf("Days Elapsed: %d | Days Remaining: %d\n", $data['days_elapsed'] ?? 0, $data['days_remaining'] ?? 0);
        $prompt .= sprintf("Total Income: M%.2f\n", $data['total_income'] ?? 0);
        $prompt .= sprintf("Total Expenses: M%.2f\n", $data['total_expenses'] ?? 0);
        $prompt .= sprintf("Net Savings: M%.2f (%.1f%% savings rate)\n", $data['net_savings'] ?? 0, $data['savings_rate'] ?? 0);
        $prompt .= sprintf("Monthly Budget: M%.2f | Remaining: M%.2f\n", $data['monthly_budget'] ?? 0, $data['remaining_budget'] ?? 0);
        $prompt .= sprintf("Total Transactions: %d\n\n", count($data['transactions'] ?? []));

        // ========== YEAR OVERVIEW (NEW) ==========
        if (isset($data['year_overview'])) {
            $yearData = $data['year_overview'];
            $prompt .= "=== YEAR OVERVIEW ({$yearData['year']}) ===\n";
            $prompt .= sprintf("Year-to-Date Income: M%.2f\n", $yearData['total_income']);
            $prompt .= sprintf("Year-to-Date Expenses: M%.2f\n", $yearData['total_expenses']);
            $prompt .= sprintf("Year-to-Date Savings: M%.2f (%.1f%% rate)\n", $yearData['total_savings'], $yearData['savings_rate']);
            $prompt .= sprintf("Year-over-Year Change: %+.1f%%\n\n", $yearData['yoy_change']);
            
            $prompt .= "Monthly Performance This Year:\n";
            foreach ($yearData['monthly_breakdown'] as $month) {
                $prompt .= sprintf("  %s: Expenses M%.2f | Income M%.2f | Savings M%.2f\n",
                    $month['full_month'],
                    $month['expenses'],
                    $month['income'],
                    $month['savings']
                );
            }
            $prompt .= "\n";
        }

        // ========== SPENDING VELOCITY ==========
        if (isset($data['spending_velocity'])) {
            $vel = $data['spending_velocity'];
            $prompt .= "=== SPENDING VELOCITY ===\n";
            $prompt .= sprintf("Current Daily Pace: M%.2f/day\n", $vel['current_pace'] ?? 0);
            $prompt .= sprintf("Historical Average Pace: M%.2f/day\n", $vel['historical_pace'] ?? 0);
            $prompt .= sprintf("Acceleration: %+.1f%% (%s trend)\n", $vel['acceleration'] ?? 0, $vel['status'] ?? 'normal');
            $prompt .= sprintf("Projected Month-End Total: M%.2f\n\n", $vel['projected_month_end'] ?? 0);
        }

        // ========== HISTORICAL TRENDS ==========
        if (!empty($data['historical_months'])) {
            $prompt .= "=== 12-MONTH HISTORICAL PATTERN ===\n";
            
            $expenses = array_column($data['historical_months'], 'expenses');
            $incomes = array_column($data['historical_months'], 'income');
            $savings = array_column($data['historical_months'], 'savings');

            $avgExpense = count($expenses) > 0 ? array_sum($expenses) / count($expenses) : 0;
            $avgIncome = count($incomes) > 0 ? array_sum($incomes) / count($incomes) : 0;
            $stdDev = $this->calculateStdDev($expenses);

            $prompt .= sprintf("Average Monthly Expense: M%.2f (σ=%.2f)\n", $avgExpense, $stdDev);
            $prompt .= sprintf("Average Monthly Income: M%.2f\n\n", $avgIncome);
        }

        // ========== CATEGORY ANALYSIS ==========
        if (!empty($data['category_budgets'])) {
            $prompt .= "=== CATEGORY BREAKDOWN ===\n";
            foreach ($data['category_budgets'] as $cat) {
                $prompt .= sprintf(
                    "%s: Budget M%.2f | Spent M%.2f (%.1f%%) | %d transactions | %+.1f%% vs 6-mo avg\n",
                    $cat['name'],
                    $cat['budget'] ?? 0,
                    $cat['expense'] ?? 0,
                    $cat['budget'] > 0 ? (($cat['expense'] / $cat['budget']) * 100) : 0,
                    $cat['transaction_count'] ?? 0,
                    $cat['vs_average'] ?? 0
                );
            }
            $prompt .= "\n";
        }

        // ========== TRANSACTIONS SAMPLE ==========
        if (!empty($data['transactions'])) {
            $expenses = array_filter($data['transactions'], fn($t) => ($t['type'] ?? '') === 'expense');
            usort($expenses, fn($a, $b) => ($b['amount'] ?? 0) <=> ($a['amount'] ?? 0));
            $top10 = array_slice($expenses, 0, 10);
            
            $prompt .= "=== TOP 10 EXPENSES ===\n";
            foreach ($top10 as $i => $txn) {
                $prompt .= sprintf(
                    "%d. M%.2f - %s [%s] on %s\n",
                    $i + 1,
                    $txn['amount'] ?? 0,
                    $txn['description'] ?? 'Unknown',
                    $txn['category'] ?? 'Unknown',
                    $txn['date'] ?? 'Unknown'
                );
            }
            $prompt .= "\n";
        }

        $prompt .= $this->getRealisticOutputSchema();

        return $prompt;
    }

    private function getRealisticOutputSchema()
    {
        return <<<'SCHEMA'
=== REQUIRED JSON OUTPUT SCHEMA ===

IMPORTANT GUIDANCE ON RECOMMENDATIONS:
- Be REALISTIC and HUMAN. Don't recommend cutting everything just because someone spent money.
- Only suggest cutting things if they're GENUINELY excessive (e.g., 200%+ over budget consistently).
- Understand that some expenses are NECESSARY (groceries, transport, utilities, healthcare).
- HIGH spending doesn't always mean BAD spending. Context matters.
- Don't give generic advice like "reduce eating out" unless it's truly excessive.
- Focus on ACTUAL problems: budget overruns, unsustainable trends, missing financial goals.
- Celebrate GOOD behavior when you see it.
- If someone is within budget and tracking well, acknowledge that instead of finding problems.

{
  "executive_summary": {
    "overall_health_score": <integer 0-100, be generous - penalize only serious issues>,
    "financial_status": "excellent|good|fair|concerning|critical",
    "key_insight": "One honest, specific sentence about their financial situation",
    "urgent_actions": [
      "Only include if there's a REAL urgent problem (over budget by 20%+, unsustainable trend, etc.)"
    ],
    "positive_highlights": [
      "Actual achievements worth celebrating",
      "Areas where they're doing well"
    ]
  },
  
  "kpi_summary": {
    "total_budget": <number>,
    "total_spent": <number>,
    "amount_remaining": <number>,
    "percent_budget_used": <0-100>,
    "transaction_count": <integer>,
    "avg_spend_per_transaction": <number>,
    "daily_burn_rate": <number>,
    "projected_month_end": <number>,
    "days_to_budget_exhaustion": <integer or null>,
    "budget_status_color": "green|yellow|red"
  },
  
  "category_analysis": [
    {
      "category": "exact category name",
      "budgeted": <number>,
      "spent": <number>,
      "remaining": <number>,
      "percent_used": <0-100+>,
      "status": "under_budget|on_track|close_to_limit|over_budget",
      "variance_vs_historical": <percentage>,
      "recommendation": "Only provide if there's an actual issue. Be realistic.",
      "top_items": ["actual items"],
      "transaction_count": <integer>,
      "avg_transaction": <number>,
      "trend": "increasing|decreasing|stable"
    }
  ],
  
  "spending_trends": {
    "monthly_trend": "increasing|decreasing|stable|volatile",
    "trend_percentage": <percentage>,
    "trend_description": "Honest 2-3 sentence description",
    "forecast_next_3_months": [
      {
        "month": "exact month name",
        "predicted_spend": <based on actual trends, not pessimistic> dont use current month data please,
        "confidence": "high|medium|low",
        "reasoning": "Brief explanation"
      }
    ],
    "unusual_spikes": [
      {
        "date": "YYYY-MM-DD",
        "amount": <number>,
        "description": "actual description",
        "category": "actual category",
        "deviation_percentage": <only include if >200% above normal>,
        "likely_reason": "reasonable interpretation"
      }
    ],
    "category_trends": []
  },
  
  "weekly_daily_insights": {
    "avg_spend_per_week": <number>,
    "weekly_breakdown": [],
    "day_of_week_pattern": {
      "highest_spending_day": "day",
      "highest_amount": <number>,
      "lowest_spending_day": "day",
      "lowest_amount": <number>,
      "pattern_interpretation": "Thoughtful insight about WHY, not judgmental"
    },
    "abnormal_days": []
  },
  
  "smart_insights": {
    "overspending_areas": [
      {
        "category": "only include if TRULY over budget (>120%)",
        "amount_over": <actual overage>,
        "specific_items": ["real items"],
        "action": "practical, achievable advice",
        "priority": "high|medium|low"
      }
    ],
    "cost_cutting_opportunities": [
      {
        "area": "only include REALISTIC opportunities",
        "potential_savings": <realistic amount>,
        "difficulty": "easy|moderate|hard",
        "impact": "high|medium|low",
        "specific_recommendation": "Practical advice, not 'stop spending'",
        "items_to_reduce": ["specific items"]
      }
    ],
    "budget_adjustments": [
      {
        "category": "only suggest if budget is clearly wrong",
        "current_budget": <number>,
        "suggested_budget": <realistic adjustment>,
        "reason": "data-driven justification"
      }
    ],
    "spending_habits": [
      "Neutral observations, not judgments"
    ],
    "anomalies": []
  },
  
  "behavioral_insights": {
    "spending_personality": "Honest description without judgment",
    "triggers": ["actual patterns observed"],
    "strengths": ["genuine strengths"],
    "weaknesses": ["only REAL problems"],
    "risk_factors": ["only if there are actual risks"],
    "recommended_changes": ["practical, achievable changes"]
  },
  
  "actionable_recommendations": [
    {
      "priority": "high|medium|low",
      "title": "Clear, non-preachy recommendation",
      "description": "Realistic explanation with actual data",
      "expected_impact": "honest impact estimate",
      "implementation_steps": [
        "Practical, specific steps"
      ],
      "difficulty": "easy|moderate|hard",
      "timeframe": "immediate|this_week|this_month",
      "category": "category or 'general'",
      "success_metric": "How to measure"
    }
  ]
}

CRITICAL RULES FOR RECOMMENDATIONS:
1. Quality over quantity - 2-3 GOOD recommendations beat 10 generic ones
2. Only recommend changes for ACTUAL problems
3. If they're doing well, SAY SO. Don't invent problems.
4. High spending ≠ bad spending. Context matters.
5. Essential categories (food, transport, utilities) - don't suggest cutting unless absurdly high
6. Be encouraging, not judgmental
7. Focus on sustainability and goals, not arbitrary frugality
8. If budget is met and savings are good, celebrate that!

Return ONLY valid JSON - no markdown, no explanations.
SCHEMA;
    }

    private function callGeminiAPI($prompt, $requestId)
    {
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
                'temperature' => 0.5,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 8192,
                'responseMimeType' => 'application/json'
            ]
        ];

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                Log::info('Calling Gemini API', ['attempt' => $attempt, 'request_id' => $requestId]);

                $response = Http::timeout(90)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-Request-ID' => $requestId
                    ])
                    ->post($url, $payload);
                
                if ($response->successful()) {
                    return $response;
                }
                
                if ($response->status() === 429 && $attempt < $this->maxRetries) {
                    $waitTime = pow(2, $attempt);
                    Log::warning('Rate limit hit, retrying', ['wait' => $waitTime]);
                    sleep($waitTime);
                    continue;
                }
                
                Log::error('API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return $response;
                
            } catch (\Exception $e) {
                Log::error('API call exception', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempt < $this->maxRetries) {
                    sleep(pow(2, $attempt));
                    continue;
                }
                throw $e;
            }
        }
        
        return null;
    }

    private function parseAIResponse($result, $requestId)
    {
        try {
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $text = $result['candidates'][0]['content']['parts'][0]['text'];
                
                $text = preg_replace('/```json\s*/i', '', $text);
                $text = preg_replace('/```\s*$/i', '', $text);
                $text = trim($text);
                
                $decoded = json_decode($text, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    if ($this->validateResponse($decoded)) {
                        Log::info('AI response validated successfully', ['request_id' => $requestId]);
                        return $decoded;
                    } else {
                        Log::warning('AI response validation failed', ['request_id' => $requestId]);
                    }
                }
                
                Log::error('JSON parsing failed', [
                    'request_id' => $requestId,
                    'error' => json_last_error_msg()
                ]);
            }
            
            return $this->getFallbackResponse();
            
        } catch (\Exception $e) {
            Log::error('Parse exception', [
                'request_id' => $requestId, 
                'error' => $e->getMessage()
            ]);
            return $this->getFallbackResponse();
        }
    }

    private function validateResponse($response)
    {
        $requiredKeys = [
            'executive_summary',
            'kpi_summary',
            'category_analysis',
            'spending_trends',
            'weekly_daily_insights',
            'smart_insights',
            'behavioral_insights',
            'actionable_recommendations'
        ];
        
        foreach ($requiredKeys as $key) {
            if (!isset($response[$key])) {
                Log::warning('Missing required key in AI response', ['key' => $key]);
                return false;
            }
        }
        
        return true;
    }

    private function checkRateLimit($requestId)
    {
        $key = 'gemini_rate_' . date('YmdHi');
        $count = Cache::get($key, 0);
        
        if ($count >= 15) {
            Log::warning('Rate limit exceeded', ['request_id' => $requestId]);
            return false;
        }
        
        Cache::put($key, $count + 1, now()->addMinute());
        return true;
    }

    private function calculateStdDev($values)
    {
        $count = count($values);
        if ($count < 2) return 0;
        
        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / $count;
        
        return sqrt($variance);
    }

    private function getFallbackResponse()
    {
        return [
            'executive_summary' => [
                'overall_health_score' => 50,
                'financial_status' => 'unknown',
                'key_insight' => 'AI analysis is temporarily unavailable. Your data is being tracked.',
                'urgent_actions' => [],
                'positive_highlights' => ['Your tracking is active']
            ],
            'kpi_summary' => [
                'total_budget' => 0,
                'total_spent' => 0,
                'amount_remaining' => 0,
                'percent_budget_used' => 0,
                'transaction_count' => 0,
                'avg_spend_per_transaction' => 0,
                'daily_burn_rate' => 0,
                'projected_month_end' => 0,
                'days_to_budget_exhaustion' => null,
                'budget_status_color' => 'yellow'
            ],
            'category_analysis' => [],
            'spending_trends' => [
                'monthly_trend' => 'unknown',
                'trend_percentage' => 0,
                'trend_description' => 'Trend analysis pending',
                'forecast_next_3_months' => [],
                'unusual_spikes' => [],
                'category_trends' => []
            ],
            'weekly_daily_insights' => [
                'avg_spend_per_week' => 0,
                'weekly_breakdown' => [],
                'day_of_week_pattern' => [
                    'highest_spending_day' => 'Unknown',
                    'highest_amount' => 0,
                    'lowest_spending_day' => 'Unknown',
                    'lowest_amount' => 0,
                    'pattern_interpretation' => 'Analysis pending'
                ],
                'abnormal_days' => []
            ],
            'smart_insights' => [
                'overspending_areas' => [],
                'cost_cutting_opportunities' => [],
                'budget_adjustments' => [],
                'spending_habits' => [],
                'anomalies' => []
            ],
            'behavioral_insights' => [
                'spending_personality' => 'Analysis pending',
                'triggers' => [],
                'strengths' => ['Consistent tracking'],
                'weaknesses' => [],
                'risk_factors' => [],
                'recommended_changes' => []
            ],
            'actionable_recommendations' => []
        ];
    }
}