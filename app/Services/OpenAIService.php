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

    public function predictExpenses($data)
    {
        try {
            // Prepare the formatted string of historical expenses for the prompt
            $formattedData = '';
            foreach ($data as $expense) {
                $formattedData .= "On {$expense['date']}, I spent M{$expense['amount']} in the {$expense['category']} category.\n";
            }

            // Create the prompt for the model
            $prompt = "Please provide a detailed prediction of my expenses for the next month, broken down by category. Here are my past expenses:\n" . $formattedData;

            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',  // or 'gpt-4'
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.2, // Adjust this value as needed
            ]);

            // Log the entire response for debugging
            \Log::info('OpenAI Response: ', (array)$response);

            // Check for predictions
            if (isset($response->choices) && count($response->choices) > 0) {
                $content = $response->choices[0]->message->content ?? 'No content available';

                // Parse the content to extract structured predictions
                $predictions = $this->parsePredictions($content);

                return [
                    'predicted_expenses' => $predictions,
                ];

            } else {
                return [
                    'error' => 'No predictions available.',
                ];
            }
        } catch (\Exception $e) {
            // Handle the exception (log it, rethrow it, etc.)
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    private function parsePredictions($content)
    {
        $lines = explode("\n", $content);
        $predictions = [];

        foreach ($lines as $line) {
            if (preg_match('/\b([A-Za-z]+):\s+M([0-9.]+)/', $line, $matches)) {
                $category = $matches[1];
                $amount = (float)$matches[2];
                $predictions[$category] = $amount;
            }
        }

        return $predictions;
    }
}
