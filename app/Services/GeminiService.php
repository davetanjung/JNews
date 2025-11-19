<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $connectTimeout;
    protected int $responseTimeout;

    public function __construct()
    {
        $config = config('services.gemini');

        $this->apiKey = $config['api_key'];
        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->model = $config['model'];
        $this->connectTimeout = $config['connect_timeout'];
        $this->responseTimeout = $config['response_timeout'];
    }

    /**
     * Generic Gemini API call
     */
    public function sendPrompt(string $prompt): ?string
    {
        Log::info('Gemini Prompt Sent', [
            'preview' => substr($prompt, 0, 500)
        ]);

        $maxRetries = 5;
        $delayMs = 300; // initial delay between retries (0.3s)

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])
                    ->timeout($this->responseTimeout)
                    ->connectTimeout($this->connectTimeout)
                    ->post("{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}", [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ]
                    ]);

                Log::info('Gemini Raw Response', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'attempt' => $attempt,
                ]);

                // ===== SUCCESS =====
                if ($response->successful()) {
                    $data = $response->json();

                    Log::info('Gemini Parsed Response', [
                        'data' => $data,
                    ]);

                    return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                }

                // ===== RETRY ONLY FOR 503 =====
                if ($response->status() === 503) {
                    Log::warning('Gemini Overloaded — Retrying...', [
                        'attempt' => $attempt,
                        'delay_ms' => $delayMs,
                    ]);

                    usleep($delayMs * 1000); // Convert ms → microseconds
                    $delayMs *= 2; // exponential backoff
                    continue;
                }

                // ===== OTHER ERRORS → FAIL IMMEDIATELY =====
                Log::error('Gemini API error (non-retryable)', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;

            } catch (\Throwable $e) {

                Log::error('Gemini Exception', [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                ]);

                // Retry exceptions too
                usleep($delayMs * 1000);
                $delayMs *= 2;
            }
        }

        // If all retries fail:
        Log::error('Gemini Failed After Max Retries', [
            'max_retries' => $maxRetries,
        ]);

        return null;
    }



    /**
     * Summarize text
     */
    public function summarize(string $text): ?string
    {
        $prompt = "Summarize the following content clearly and concisely:\n\n{$text}";

        return $this->sendPrompt($prompt);
    }

    /**
     * Extend/lengthen text
     */
    public function extendContent(string $content, string $description, int $targetWords = 300): ?string
    {
        $prompt = "
Rewrite and expand the following content into at least {$targetWords} words.
Maintain meaning but improve clarity, depth, and structure.

Content:
{$content}, Description: {$description}
        ";

        return $this->sendPrompt($prompt);
    }

    /**
     * Extract keywords (optional)
     */
    public function extractKeywords(string $text): ?string
    {
        $prompt = "Extract 5–10 important keywords from the following text:\n\n{$text}";

        return $this->sendPrompt($prompt);
    }
}
