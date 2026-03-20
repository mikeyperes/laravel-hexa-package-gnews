<?php

namespace hexa_package_gnews\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use hexa_core\Models\Setting;

class GNewsService
{
    /**
     * @return string|null
     */
    private function getApiKey(): ?string
    {
        return Setting::getValue('gnews_api_key');
    }

    /**
     * Test the API key.
     *
     * @param string|null $apiKey Override key to test.
     * @return array{success: bool, message: string}
     */
    public function testApiKey(?string $apiKey = null): array
    {
        $key = $apiKey ?? $this->getApiKey();
        if (!$key) {
            return ['success' => false, 'message' => 'No GNews API key configured.'];
        }

        try {
            $response = Http::timeout(10)
                ->get('https://gnews.io/api/v4/top-headlines', [
                    'token' => $key,
                    'lang' => 'en',
                    'max' => 1,
                ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'GNews API key is valid.'];
            }
            if ($response->status() === 401 || $response->status() === 403) {
                return ['success' => false, 'message' => 'Invalid or expired API key.'];
            }
            return ['success' => false, 'message' => "GNews returned HTTP {$response->status()}."];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Search for articles.
     *
     * @param string $query
     * @param int $max Results (max 10 on free tier).
     * @param string $lang Language code.
     * @return array{success: bool, message: string, data: array|null}
     */
    public function searchArticles(string $query, int $max = 10, string $lang = 'en'): array
    {
        $key = $this->getApiKey();
        if (!$key) {
            return ['success' => false, 'message' => 'No GNews API key configured.', 'data' => null];
        }

        try {
            $response = Http::timeout(15)
                ->get('https://gnews.io/api/v4/search', [
                    'token' => $key,
                    'q' => $query,
                    'lang' => $lang,
                    'max' => min($max, 10),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $articles = collect($data['articles'] ?? [])->map(fn($a) => [
                    'source_api' => 'gnews',
                    'title' => $a['title'] ?? '',
                    'description' => $a['description'] ?? '',
                    'content' => $a['content'] ?? '',
                    'url' => $a['url'] ?? '',
                    'image' => $a['image'] ?? null,
                    'published_at' => $a['publishedAt'] ?? null,
                    'source_name' => $a['source']['name'] ?? '',
                    'source_url' => $a['source']['url'] ?? '',
                    'author' => null,
                    'categories' => [],
                    'keywords' => [],
                    'language' => $lang,
                    'country' => null,
                ])->toArray();

                return [
                    'success' => true,
                    'message' => count($articles) . ' articles found.',
                    'data' => ['articles' => $articles, 'total' => $data['totalArticles'] ?? count($articles)],
                ];
            }

            return ['success' => false, 'message' => "GNews returned HTTP {$response->status()}.", 'data' => null];
        } catch (\Exception $e) {
            Log::error('GNewsService::searchArticles error', ['query' => $query, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'data' => null];
        }
    }
}
