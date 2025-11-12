<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GNewsService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.gnews.base_url', env('GNEWS_BASE_URL'));
        $this->apiKey = config('services.gnews.api_key', env('GNEWS_API_KEY'));
    }

    protected function headers()
    {
        // GNews supports 'X-Api-Key' header or apikey query param
        return [
            'X-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ];
    }

    protected function get($endpoint, $params = [])
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $cacheKey = 'gnews:' . md5($url . json_encode($params));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($url, $params) {
            // append API key ke header
            $response = Http::withHeaders($this->headers())
                            ->timeout(10)
                            ->get($url, $params);

            if ($response->ok()) {
                return $response->json();
            }

            // Throw an exception you can catch in controllers
            $status = $response->status();
            $body = $response->body();
            throw new \Exception("GNews API error: HTTP $status - $body", $status);
        });
    }

    // Search endpoint example
    public function search(string $q, array $options = [])
    {
        $params = array_merge([
            'q' => $q,
            'lang' => $options['lang'] ?? 'en',
            'max' => $options['max'] ?? 10,
            'page' => $options['page'] ?? 1,
            'sortby' => $options['sortby'] ?? 'publishedAt', // or relevance
        ], $options);

        return $this->get('search', $params);
    }

    // Top headlines example
    public function topHeadlines(array $options = [])
    {
        $params = array_merge([
            'category' => $options['category'] ?? 'general',
            'lang' => $options['lang'] ?? null,
            'country' => $options['country'] ?? null,
            'max' => $options['max'] ?? 10,
        ], $options);

        // Remove nulls
        $params = array_filter($params, fn($v) => !is_null($v) && $v !== '');

        return $this->get('top-headlines', $params);
    }
}
