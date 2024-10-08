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
            $formattedData .= "On {$expense['date']}, I spent M{$expense['amount']} on {$expense['description']} in the {$expense['category']} category.\n";
        }

        // Create the prompt for the model
        $prompt = "Based on these historical expenses, predict future expenses:\n" . $formattedData;

        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',  // or 'gpt-3.5-turbo'
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        // Log the entire response for debugging
        \Log::info('OpenAI Response: ', (array)$response);

        // Check for predictions
        if (isset($response->choices) && count($response->choices) > 0) {
            return json_decode($response->choices[0]->message->content, true);
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

    
    
}
