<?php

namespace App\Services;

// Reverting to Laravel's Http Facade
use Illuminate\Support\Facades\Http;
use Throwable;

class GNewsService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.gnews.base_url', env('GNEWS_BASE_URL'));
        $this->apiKey = config('services.gnews.api_key', env('GNEWS_API_KEY'));
        
        // Explicit check for API Key
        if (empty($this->apiKey)) {
            throw new \Exception("GNEWS_API_KEY is missing in your .env or config/services.php file.", 500);
        }
    }

    protected function headers()
    {
        // Use the API key in the header
        return [
            'X-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ];
    }

    protected function get($endpoint, $params = [])
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        // *** FIX: Using Http Facade Directly (no external client) ***
        $response = Http::withHeaders($this->headers())
                        ->timeout(10)
                        ->get($url, $params);

        if ($response->ok()) {
            $data = $response->json();
            
            // Check for valid JSON structure even on 200 OK
            if (is_array($data) && !isset($data['articles']) && isset($data['totalArticles'])) {
                 // GNews often returns 200 OK with an empty array if filters are bad
                 return $data;
            }

            return $data;
        }

        $status = $response->status();
        $body = $response->body();
        throw new \Exception("GNews API error: HTTP $status - $body", $status);
    }

    // Search endpoint example
    public function search(string $q, array $options = [])
    {
        $params = array_merge([
            'q' => $q,
            'lang' => $options['lang'] ?? 'en',
            'max' => $options['max'] ?? 20, 
            'page' => $options['page'] ?? 1,
            'sortby' => $options['sortby'] ?? 'publishedAt',
        ], $options);

        return $this->get('search', $params);
    }

    // Top headlines example
    public function topHeadlines(array $options = [])
    {
        $params = array_merge([
            // Mandating filters for US/English to maximize successful results
            'category' => $options['category'] ?? 'general', 
            'lang' => $options['lang'] ?? 'en',             
            'country' => $options['country'] ?? 'us',        
            'max' => $options['max'] ?? 20, 
        ], $options);

        $params = array_filter($params, fn($v) => !is_null($v) && $v !== '');

        return $this->get('top-headlines', $params);
    }
}