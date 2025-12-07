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
    private $model = 'gemini-2.0-flash-exp';
    private $maxRetries = 3;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        
        if (empty($this->apiKey)) {
            Log::error('GEMINI_API_KEY is not set in environment');
        }
    }

    /**
     * Main analysis method - comprehensive AI insights
     */
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

        // Create cache key based on data
        $cacheKey = 'gemini_analysis_' . md5(json_encode([
            'transactions' => count($data['transactions'] ?? []),
            'total_expenses' => $data['total_expenses'] ?? 0,
            'total_income' => $data['total_income'] ?? 0,
            'month' => $data['period_start'] ?? '',
            'categories' => count($data['category_budgets'] ?? [])
        ]));
        
        // Check cache (30 minutes)
        if (Cache::has($cacheKey)) {
            Log::info('Returning cached AI analysis', ['request_id' => $requestId]);
            return Cache::get($cacheKey);
        }

        // Rate limiting
        if (!$this->checkRateLimit($requestId)) {
            return $this->getFallbackResponse();
        }

        try {
            $prompt = $this->buildEnhancedPrompt($data);
            
            $response = $this->callGeminiAPI($prompt, $requestId);

            if ($response && $response->successful()) {
                $result = $response->json();
                $parsed = $this->parseAIResponse($result, $requestId);
                
                // Cache for 30 minutes
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

    /**
     * Build comprehensive prompt with enhanced analysis requirements
     */
    private function buildEnhancedPrompt(array $data)
    {
        $prompt = "You are an expert financial analyst AI with expertise in personal finance, behavioral economics, predictive modeling, and financial planning. Analyze this comprehensive financial dataset and provide actionable, personalized insights.\n\n";
        
        // ========== CURRENT PERIOD OVERVIEW ==========
        $prompt .= "=== CURRENT PERIOD ANALYSIS ===\n";
        $prompt .= sprintf("Period: %s to %s\n", $data['period_start'] ?? 'Unknown', $data['period_end'] ?? 'Unknown');
        $prompt .= sprintf("Days Elapsed: %d | Days Remaining: %d\n", $data['days_elapsed'] ?? 0, $data['days_remaining'] ?? 0);
        $prompt .= sprintf("Total Income: M%.2f\n", $data['total_income'] ?? 0);
        $prompt .= sprintf("Total Expenses: M%.2f\n", $data['total_expenses'] ?? 0);
        $prompt .= sprintf("Net Savings: M%.2f (%.1f%% savings rate)\n", $data['net_savings'] ?? 0, $data['savings_rate'] ?? 0);
        $prompt .= sprintf("Monthly Budget: M%.2f | Remaining: M%.2f\n", $data['monthly_budget'] ?? 0, $data['remaining_budget'] ?? 0);
        $prompt .= sprintf("Total Transactions: %d\n\n", count($data['transactions'] ?? []));

        // ========== SPENDING VELOCITY ==========
        if (isset($data['spending_velocity'])) {
            $vel = $data['spending_velocity'];
            $prompt .= "=== SPENDING VELOCITY & MOMENTUM ===\n";
            $prompt .= sprintf("Current Daily Pace: M%.2f/day\n", $vel['current_pace'] ?? 0);
            $prompt .= sprintf("Historical Average Pace: M%.2f/day\n", $vel['historical_pace'] ?? 0);
            $prompt .= sprintf("Acceleration: %+.1f%% (%s trend)\n", $vel['acceleration'] ?? 0, $vel['status'] ?? 'normal');
            $prompt .= sprintf("Projected Month-End Total: M%.2f\n\n", $vel['projected_month_end'] ?? 0);
        }

        // ========== HISTORICAL TREND ANALYSIS ==========
        if (!empty($data['historical_months'])) {
            $prompt .= "=== 12-MONTH HISTORICAL PATTERN ===\n";
            
            $expenses = array_column($data['historical_months'], 'expenses');
            $incomes = array_column($data['historical_months'], 'income');
            $savings = array_column($data['historical_months'], 'savings');

            $avgExpense = count($expenses) > 0 ? array_sum($expenses) / count($expenses) : 0;
            $avgIncome = count($incomes) > 0 ? array_sum($incomes) / count($incomes) : 0;
            $avgSavings = count($savings) > 0 ? array_sum($savings) / count($savings) : 0;
            $stdDev = $this->calculateStdDev($expenses);

            $prompt .= sprintf("Average Monthly Expense: M%.2f (σ=%.2f)\n", $avgExpense, $stdDev);
            $prompt .= sprintf("Average Monthly Income: M%.2f\n", $avgIncome);
            $prompt .= sprintf("Average Monthly Savings: M%.2f (%.1f%% rate)\n", $avgSavings, $avgIncome > 0 ? ($avgSavings / $avgIncome * 100) : 0);
            
            if ($avgExpense > 0) {
                $volatility = ($stdDev / $avgExpense) * 100;
                $prompt .= sprintf("Spending Volatility: %.1f%% (coefficient of variation)\n\n", $volatility);
            }
            
            $prompt .= "Monthly Breakdown:\n";
            foreach ($data['historical_months'] as $month) {
                $variance = $avgExpense > 0 ? ((($month['expenses'] ?? 0) - $avgExpense) / $avgExpense) * 100 : 0;
                $prompt .= sprintf(
                    "  %s: Expenses M%.2f (%+.1f%% vs avg) | Income M%.2f | Saved M%.2f (%.1f%% rate)\n",
                    $month['month'],
                    $month['expenses'] ?? 0,
                    $variance,
                    $month['income'] ?? 0,
                    $month['savings'] ?? 0,
                    $month['savings_rate'] ?? 0
                );
            }
            $prompt .= "\n";
        }

        // ========== CATEGORY DEEP DIVE ==========
        if (!empty($data['category_budgets'])) {
            $prompt .= "=== CATEGORY ANALYSIS ===\n";
            foreach ($data['category_budgets'] as $cat) {
                $prompt .= sprintf(
                    "%s: Budget M%.2f | Spent M%.2f (%.1f%%) | Remaining M%.2f | %d transactions (M%.2f avg) | %+.1f%% vs 6-month avg\n",
                    $cat['name'],
                    $cat['budget'] ?? 0,
                    $cat['expense'] ?? 0,
                    $cat['budget'] > 0 ? (($cat['expense'] / $cat['budget']) * 100) : 0,
                    $cat['remaining'] ?? 0,
                    $cat['transaction_count'] ?? 0,
                    $cat['avg_transaction'] ?? 0,
                    $cat['vs_average'] ?? 0
                );
            }
            $prompt .= "\n";
        }

        // ========== TRANSACTION PATTERNS ==========
        if (!empty($data['transactions'])) {
            $prompt .= "=== TRANSACTION-LEVEL INSIGHTS ===\n";
            $prompt .= sprintf("Total Transactions: %d\n\n", count($data['transactions']));
            
            // By Category
            $byCategory = $this->groupBy($data['transactions'], 'category');
            $prompt .= "BY CATEGORY:\n";
            foreach ($byCategory as $cat => $items) {
                $total = array_sum(array_column($items, 'amount'));
                $count = count($items);
                $avg = $count > 0 ? $total / $count : 0;
                $percentOfTotal = ($data['total_expenses'] ?? 0) > 0 ? ($total / ($data['total_expenses'] ?? 0)) * 100 : 0;
                $prompt .= sprintf("  %s: M%.2f (%.1f%% of total, %d txns, M%.2f avg)\n", $cat, $total, $percentOfTotal, $count, $avg);
            }
            $prompt .= "\n";

            // By Day of Week
            $byDay = $this->groupBy($data['transactions'], 'day_of_week');
            $prompt .= "BY DAY OF WEEK:\n";
            $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($dayOrder as $day) {
                if (isset($byDay[$day])) {
                    $items = $byDay[$day];
                    $total = array_sum(array_column($items, 'amount'));
                    $prompt .= sprintf("  %s: M%.2f (%d txns)\n", $day, $total, count($items));
                }
            }
            $prompt .= "\n";

            // Recurring Expenses
            $byDescription = $this->groupBy($data['transactions'], 'description');
            $recurring = array_filter($byDescription, fn($items) => count($items) >= 2);
            
            if (!empty($recurring)) {
                $prompt .= "RECURRING EXPENSES (2+ occurrences):\n";
                foreach ($recurring as $desc => $items) {
                    $total = array_sum(array_column($items, 'amount'));
                    $avg = count($items) > 0 ? $total / count($items) : 0;
                    $prompt .= sprintf("  \"%s\": %d times, M%.2f total, M%.2f avg\n", $desc, count($items), $total, $avg);
                }
                $prompt .= "\n";
            }

            // Top 10 Expenses
            $expenses = array_filter($data['transactions'], fn($t) => ($t['type'] ?? '') === 'expense');
            usort($expenses, fn($a, $b) => ($b['amount'] ?? 0) <=> ($a['amount'] ?? 0));
            $top10 = array_slice($expenses, 0, 10);
            
            $prompt .= "TOP 10 LARGEST EXPENSES:\n";
            foreach ($top10 as $i => $txn) {
                $prompt .= sprintf(
                    "  %d. M%.2f - %s [%s] on %s (%s)\n",
                    $i + 1,
                    $txn['amount'] ?? 0,
                    $txn['description'] ?? 'Unknown',
                    $txn['category'] ?? 'Unknown',
                    $txn['date'] ?? 'Unknown',
                    $txn['day_of_week'] ?? 'Unknown'
                );
            }
            $prompt .= "\n";

            // Anomaly Detection
            $expenseCount = count($expenses);
            $avgTransaction = $expenseCount > 0 ? (($data['total_expenses'] ?? 0) / $expenseCount) : 0;
            $threshold = $avgTransaction * 3;
            $anomalies = $expenseCount > 0 ? array_filter($expenses, fn($t) => ($t['amount'] ?? 0) > $threshold) : [];

            if (!empty($anomalies)) {
                $prompt .= "UNUSUAL TRANSACTIONS (>3x average):\n";
                foreach ($anomalies as $txn) {
                    $deviation = $avgTransaction > 0 ? ((($txn['amount'] ?? 0) - $avgTransaction) / $avgTransaction) * 100 : 0;
                    $prompt .= sprintf(
                        "  M%.2f - %s [%s] on %s (+%.0f%% vs avg)\n",
                        $txn['amount'] ?? 0,
                        $txn['description'] ?? 'Unknown',
                        $txn['category'] ?? 'Unknown',
                        $txn['date'] ?? 'Unknown',
                        $deviation
                    );
                }
                $prompt .= "\n";
            }
        }

        // ========== WEEKLY & DAILY PATTERNS ==========
        if (isset($data['weekly_breakdown'])) {
            $prompt .= "=== WEEKLY BREAKDOWN ===\n";
            foreach ($data['weekly_breakdown'] as $week) {
                $prompt .= sprintf("Week %d (%s to %s): M%.2f (%s)\n", 
                    $week['week_number'],
                    $week['start_date'],
                    $week['end_date'],
                    $week['total'],
                    $week['status']
                );
            }
            $prompt .= "\n";
        }

        if (isset($data['daily_pattern'])) {
            $prompt .= "=== DAILY SPENDING PATTERN ===\n";
            foreach ($data['daily_pattern']['by_day'] as $day => $stats) {
                $prompt .= sprintf("%s: M%.2f total, %d txns, M%.2f avg per transaction\n",
                    $day, $stats['total'], $stats['count'], $stats['average']
                );
            }
            $prompt .= "\n";
        }

        // ========== OUTPUT SCHEMA ==========
        $prompt .= $this->getOutputSchema();

        return $prompt;
    }

    /**
     * Enhanced JSON output schema with more detailed requirements
     */
    private function getOutputSchema()
    {
        return <<<'SCHEMA'
=== REQUIRED JSON OUTPUT SCHEMA ===

Analyze ALL the data comprehensively and provide intelligent, data-driven insights in this exact JSON structure:

{
  "executive_summary": {
    "overall_health_score": <integer 0-100, calculate based on: budget adherence (30%), savings rate (30%), spending stability (20%), trend direction (20%)>,
    "financial_status": "excellent|good|fair|concerning|critical",
    "key_insight": "One powerful, specific sentence about the user's current financial situation with actual numbers",
    "urgent_actions": [
      "Specific action item based on actual overspending or concerning trends",
      "Another urgent action if needed"
    ],
    "positive_highlights": [
      "Actual achievement from the data (e.g., 'Saved 15% more than last month')",
      "Another positive observation"
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
      "category": "exact category name from data",
      "budgeted": <number>,
      "spent": <number>,
      "remaining": <number>,
      "percent_used": <0-100+>,
      "status": "under_budget|on_track|close_to_limit|over_budget",
      "variance_vs_historical": <percentage difference vs 6-month average>,
      "recommendation": "Specific, actionable advice for this category",
      "top_items": ["actual transaction 1", "actual transaction 2"],
      "transaction_count": <integer>,
      "avg_transaction": <number>,
      "trend": "increasing|decreasing|stable"
    }
  ],
  
  "spending_trends": {
    "monthly_trend": "increasing|decreasing|stable|volatile",
    "trend_percentage": <percentage change over last 3-6 months>,
    "trend_description": "Detailed 2-3 sentence explanation of the trend with specific numbers",
    "forecast_next_3_months": [
      {
        "month": "exact month name (e.g., 'January 2025')",
        "predicted_spend": <calculate based on: historical average + current trend + seasonal factors>,
        "confidence": "high|medium|low",
        "reasoning": "Brief explanation of prediction basis"
      },
      {
        "month": "next month",
        "predicted_spend": <number>,
        "confidence": "high|medium|low",
        "reasoning": "explanation"
      },
      {
        "month": "third month",
        "predicted_spend": <number>,
        "confidence": "high|medium|low",
        "reasoning": "explanation"
      }
    ],
    "unusual_spikes": [
      {
        "date": "YYYY-MM-DD",
        "amount": <number>,
        "description": "actual transaction description",
        "category": "actual category",
        "deviation_percentage": <how much % above normal>,
        "likely_reason": "inferred reason based on description and context"
      }
    ],
    "category_trends": [
      {
        "category": "name",
        "trend": "increasing|decreasing|stable",
        "change_percent": <based on recent vs historical>,
        "interpretation": "What this means and recommended action"
      }
    ]
  },
  
  "weekly_daily_insights": {
    "avg_spend_per_week": <number>,
    "weekly_breakdown": [
      {
        "week": "Week 1",
        "total": <from data>,
        "status": "high|normal|low",
        "notable_items": ["significant transaction if any"]
      }
    ],
    "day_of_week_pattern": {
      "highest_spending_day": "actual day",
      "highest_amount": <number>,
      "lowest_spending_day": "actual day",
      "lowest_amount": <number>,
      "pattern_interpretation": "Detailed insight about WHY this pattern exists based on transaction types and categories"
    },
    "abnormal_days": [
      {
        "date": "YYYY-MM-DD",
        "amount": <number>,
        "reason": "why this was abnormal",
        "day_of_week": "day name"
      }
    ]
  },
  
  "smart_insights": {
    "overspending_areas": [
      {
        "category": "name",
        "amount_over": <vs budget or historical>,
        "specific_items": ["actual transaction", "another transaction"],
        "action": "specific recommendation",
        "priority": "high|medium|low"
      }
    ],
    "cost_cutting_opportunities": [
      {
        "area": "specific area based on data",
        "potential_savings": <realistic monthly number>,
        "difficulty": "easy|moderate|hard",
        "impact": "high|medium|low",
        "specific_recommendation": "actionable step-by-step advice",
        "items_to_reduce": ["recurring expense 1", "recurring expense 2"]
      }
    ],
    "budget_adjustments": [
      {
        "category": "name",
        "current_budget": <number>,
        "suggested_budget": <based on historical average + 10% buffer>,
        "reason": "data-driven justification with numbers"
      }
    ],
    "spending_habits": [
      "Specific observation from actual patterns (e.g., 'You spend 40% more on weekends')",
      "Another habit observation"
    ],
    "anomalies": [
      "Unusual pattern with specifics",
      "Another anomaly if exists"
    ]
  },
  
  "behavioral_insights": {
    "spending_personality": "Detailed description of spending behavior (e.g., 'Disciplined weekday spender with weekend splurges', 'Consistent saver with occasional impulse purchases')",
    "triggers": [
      "What causes spending spikes based on data patterns"
    ],
    "strengths": [
      "Positive behavior observed (with specifics)"
    ],
    "weaknesses": [
      "Area needing improvement (with specifics)"
    ],
    "risk_factors": [
      "Potential financial risk from observed patterns"
    ],
    "recommended_changes": [
      "Behavioral change recommendation 1",
      "Behavioral change recommendation 2"
    ]
  },
  
  "actionable_recommendations": [
    {
      "priority": "high|medium|low",
      "title": "Clear, actionable recommendation title",
      "description": "Detailed explanation with actual numbers and reasoning from the data",
      "expected_impact": "M<number> monthly savings" or "specific outcome",
      "implementation_steps": [
        "Specific step 1",
        "Specific step 2",
        "Specific step 3"
      ],
      "difficulty": "easy|moderate|hard",
      "timeframe": "immediate|this_week|this_month",
      "category": "actual category name or 'general'",
      "success_metric": "How to measure success"
    }
  ]
}

CRITICAL REQUIREMENTS:
1. Use EXACT numbers, dates, descriptions, and categories from the provided data
2. Calculate health score mathematically: (budget_adherence * 0.3) + (savings_rate_score * 0.3) + (stability_score * 0.2) + (trend_score * 0.2)
3. Forecast must be based on: historical average + (current trend * weight) + volatility consideration
4. Identify anomalies as transactions >3x the average transaction amount
5. All percentages must be mathematically accurate to 1 decimal place
6. Provide minimum 5-7 actionable recommendations ranked by priority and impact
7. Pattern interpretations must cite actual transaction data
8. Be honest and direct - if overspending is severe, state it clearly with numbers
9. Return ONLY valid JSON - no markdown blocks, no explanations outside JSON
10. Every insight must be backed by specific data points from the analysis
11. Forecasts should account for detected trends (increasing/decreasing/stable/volatile)
12. Budget adjustment suggestions must be realistic based on 6-12 month averages

SCHEMA;
    }

    /**
     * Call Gemini API with enhanced configuration
     */
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
                'temperature' => 0.4, // Slightly higher for more creative insights
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

    /**
     * Parse AI response with enhanced validation
     */
    private function parseAIResponse($result, $requestId)
    {
        try {
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $text = $result['candidates'][0]['content']['parts'][0]['text'];
                
                // Clean any markdown
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

    /**
     * Validate response structure
     */
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

    /**
     * Rate limiting check
     */
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

    /**
     * Calculate standard deviation
     */
    private function calculateStdDev($values)
    {
        $count = count($values);
        if ($count < 2) return 0;
        
        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / $count;
        
        return sqrt($variance);
    }

    /**
     * Group array by key
     */
    private function groupBy($array, $key)
    {
        $result = [];
        foreach ($array as $item) {
            $groupKey = $item[$key] ?? 'unknown';
            if (!isset($result[$groupKey])) {
                $result[$groupKey] = [];
            }
            $result[$groupKey][] = $item;
        }
        return $result;
    }

    /**
     * Enhanced fallback response
     */
    private function getFallbackResponse()
    {
        return [
            'executive_summary' => [
                'overall_health_score' => 50,
                'financial_status' => 'unknown',
                'key_insight' => 'AI analysis is temporarily unavailable. Your financial data has been recorded and basic metrics are available.',
                'urgent_actions' => ['Review your budget categories', 'Check API configuration if this persists'],
                'positive_highlights' => ['Your data tracking is active and functioning']
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
                'trend_description' => 'Trend analysis pending AI service restoration',
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
                    'pattern_interpretation' => 'Pattern analysis pending'
                ],
                'abnormal_days' => []
            ],
            'smart_insights' => [
                'overspending_areas' => [],
                'cost_cutting_opportunities' => [],
                'budget_adjustments' => [],
                'spending_habits' => ['Manual review recommended'],
                'anomalies' => []
            ],
            'behavioral_insights' => [
                'spending_personality' => 'Analysis pending - check back after AI service restoration',
                'triggers' => [],
                'strengths' => ['Consistent data tracking'],
                'weaknesses' => [],
                'risk_factors' => [],
                'recommended_changes' => ['Enable AI analysis for personalized insights']
            ],
            'actionable_recommendations' => [
                [
                    'priority' => 'medium',
                    'title' => 'Review Your Spending Manually',
                    'description' => 'While AI analysis is unavailable, review your recent transactions and compare against your budget',
                    'expected_impact' => 'Better awareness of spending patterns',
                    'implementation_steps' => [
                        'Review top 10 largest expenses',
                        'Check category budgets vs actual spending',
                        'Identify any unusual transactions'
                    ],
                    'difficulty' => 'easy',
                    'timeframe' => 'immediate',
                    'category' => 'general',
                    'success_metric' => 'Completed manual review'
                ]
            ]
        ];
    }
}