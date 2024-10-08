<?php

namespace App\Services;

use OpenAI;

class OpenAIService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
    }

    public function predictExpenses($data, $currentBudget)
    {
        try {
            // Prepare the formatted string of historical expenses for the prompt
            $formattedData = '';
            foreach ($data as $expense) {
                $formattedData .= "On {$expense['date']}, I spent M{$expense['amount']} in the {$expense['category']} category.\n";
            }

            // Create the prompt for the model
            $prompt = "Based on my total budget of M{$currentBudget} this month and my spending habits, please provide a detailed prediction of my budget, total expenditure, and how much I will save for the next month. Here are my past expenses:\n" . $formattedData;

            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',  
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.2, // Adjust for a more deterministic response
            ]);

            // Log the entire response for debugging
            \Log::info('OpenAI Response: ', (array)$response);

            // Check for predictions
            if (isset($response->choices) && count($response->choices) > 0) {
                $content = $response->choices[0]->message->content ?? 'No content available';
    
                // Parse the predictions into an array
                $predictionsArray = $this->parsePredictions($content);
    
                // Calculate total predicted expenses
                $totalPredictedExpenses = array_sum($predictionsArray);
                
                // Calculate projected savings
                $projectedSavings = $currentBudget - $totalPredictedExpenses;
    
                // Return as array instead of JSON
                return [
                    'predicted_expenses' => $predictionsArray,
                    'total_predicted_expenses' => $totalPredictedExpenses,
                    'projected_savings' => $projectedSavings
                ];
            } else {
                return response()->json([
                    'error' => 'No predictions available.',
                ]);
            }
        } catch (\Exception $e) {
            // Handle the exception (log it, rethrow it, etc.)
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function parsePredictions($content)
    {
        $predictions = [];
        
        // Match lines with the expected format "Category: Amount"
        preg_match_all('/([A-Za-z\s]+):\s*([M]?[\d,\.]+)/', $content, $matches);
        
        foreach ($matches[1] as $index => $category) {
            $amount = (float)str_replace(['M', ','], '', $matches[2][$index]); // Remove 'M' and commas, then convert to float
            if (!empty(trim($category))) { // Ensure category name is not empty
                $predictions[trim($category)] = $amount; // Trim to remove extra spaces
            }
        }

        return $predictions;
    }
}
