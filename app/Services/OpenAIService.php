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
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Based on these historical expenses, predict future expenses: ' . json_encode($data)
                    ],
                ],
            ]);
    
            // Assuming the response structure follows the OpenAI API response format
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            // Handle the exception (log it, rethrow it, etc.)
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
    
    
}
