<?php

namespace Ja\Notion\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Notion
{
    private string $token;

    private string $endpointPrefix = 'https://api.notion.com/v1';

    private string $version = '2022-06-28';

    private array $methods = ['get', 'patch', 'post', 'delete'];

    public function __construct()
    {
        $this->token = env('NOTION_API_TOKEN', null);
    }

    private function request(string $endpoint, array $data = [], string $method = null): array|null
    {
        if (!in_array($method, ['get', 'post', 'put', 'delete'])) {
            $method = 'get';
        }

        $headers = $this->headers();
        $url = $this->endpoint($endpoint);

        $getResponse = fn () => Http::withHeaders($headers)->$method($url, $data)['results'] ?? null;

        // Check cache for results and set cache if none found
        if ($this->cache) {
            return Cache::get($endpoint, fn () => (
                ($results = $getResponse()) !== null
                    ? Cache::put($endpoint, $results)
                    : null
            ));
        }

        return $getResponse();
    }

    public function __call($name, $arguments)
    {
        if ($this->methodExists($method = $name)) {
            list($endpoint, $data) = $arguments;

            return $this->request($endpoint, $data, $method);
        }

        return null;
    }

    private function methodExists(string $method): bool
    {
        return in_array(strtolower($method), $this->methods);
    }
    
    private function headers(array $merge = []): array
    {
        return array_merge([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
            'Notion-Version' => $this->version,
        ], $merge);
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