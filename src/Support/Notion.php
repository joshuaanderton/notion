<?php

namespace Ja\Notion\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Notion
{
    private string $token;

    private string $endpointPrefix = 'https://api.notion.com/v1';

    private string $version = '2022-06-28';

    public function __construct()
    {
        $this->token = env('NOTION_API_TOKEN', null);
    }

    public function results(string $endpoint, array $data = [], string $method = null, bool $cache = null): array|null
    {
        $getResults = fn () => $this->request($endpoint, $data, $method)['results'] ?? null;

        // Check cache for results and set cache if none found
        if ($cache === false) {
            return $getResults();
        }
        
        $cacheKey = base64_encode(json_encode([$endpoint, $data], true));

        return Cache::get($cacheKey, function () use ($cacheKey, $getResults) {
            $results = $getResults();
            
            if ($results !== null) {
                Cache::put($cacheKey, $results);
            }
            
            return $results;
        });
    }

    public function request(string $endpoint, array $data = [], string $method = null): Response
    {
        if (!in_array($method, ['get', 'post', 'put', 'delete'])) {
            $method = 'get';
        }

        $headers = $this->headers();
        
        $url = $this->endpoint($endpoint);

        return Http::withHeaders($headers)->$method($url, $data);
    }
    
    private function headers(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
            'Notion-Version' => $this->version,
        ];
    }

    private function endpoint(string $endpoint)
    {
        $endpoint = trim($endpoint, '/');

        return "{$this->endpointPrefix}/{$endpoint}";
    }

    // Static method for scheduling routine checks for changes to cached Notion pages
    // public static function checkForUpdates(string $token = null, string $version = null, bool $cache = null)
    // {
    //     $notion = new self($token, $version, $cache);
    //     $store = Cache::get("blazervel_notion_last_edited_times");
    // }
}