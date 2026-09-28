<?php

namespace hexa_package_gnews\Services;

use hexa_core\Security\Http\OutboundHttpResponse;
use hexa_core\Security\Http\SafeOutboundHttpClient;
use Illuminate\Support\Facades\Log;

class GNewsService
{
    public function __construct(private readonly ?SafeOutboundHttpClient $http = null) {}

    private function request(string $endpoint, array $query, int $timeout = 15): OutboundHttpResponse
    {
        return ($this->http ?? app(SafeOutboundHttpClient::class))->request(
            'GET',
            'https://gnews.io/api/v4/'.$endpoint.'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986),
            ['timeout' => $timeout, 'max_bytes' => 2 * 1024 * 1024, 'max_redirects' => 0],
        );
    }

    private function getApiKey(): ?string
    {
        return app(\hexa_core\Services\CredentialService::class)->get('gnews', 'api_key');
    }

    /**
     * Test the API key.
     *
     * @param  string|null  $apiKey  Override key to test.
     * @return array{success: bool, message: string}
     */
    public function testApiKey(?string $apiKey = null): array
    {
        $key = $apiKey ?? $this->getApiKey();
        if (! $key) {
            return ['success' => false, 'message' => 'No GNews API key configured.'];
        }

        try {
            $response = $this->request('top-headlines', [
                'token' => $key,
                'lang' => 'en',
                'max' => 1,
            ], 10);

            if ($response->successful() && is_array($response->json()['articles'] ?? null)) {
                return ['success' => true, 'message' => 'GNews API key is valid.'];
            }
            if ($response->status === 401 || $response->status === 403) {
                return ['success' => false, 'message' => 'Invalid or expired API key.'];
            }

            return ['success' => false, 'message' => "GNews returned an invalid response (HTTP {$response->status})."];
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'GNews could not be reached securely.'];
        }
    }

    /**
     * Search for articles.
     *
     * @param  int  $max  Results (max 10 on free tier).
     * @param  string  $lang  Language code.
     * @return array{success: bool, message: string, data: array|null}
     */
    public function searchArticles(string $query, int $max = 10, string $lang = 'en', ?string $country = null): array
    {
        $key = $this->getApiKey();
        if (! $key) {
            return ['success' => false, 'message' => 'No GNews API key configured.', 'data' => null];
        }

        try {
            $response = $this->request('search', [
                'token' => $key,
                'q' => $query,
                'lang' => $lang,
                'max' => max(1, min($max, 10)),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (! is_array($data) || ! is_array($data['articles'] ?? null)) {
                    return ['success' => false, 'message' => 'GNews returned an invalid article response.', 'data' => null];
                }
                $allowedCountries = $this->allowedCountries($country);
                $articles = collect($data['articles'])
                    ->filter(static fn ($article): bool => is_array($article))
                    ->filter(static function (array $article) use ($allowedCountries): bool {
                        if ($allowedCountries === []) {
                            return true;
                        }
                        $sourceCountry = strtolower(trim((string) ($article['source']['country'] ?? '')));

                        // Keep anything GNews does not label rather than
                        // discarding a usable story on missing metadata.
                        return $sourceCountry === '' || in_array($sourceCountry, $allowedCountries, true);
                    })
                    ->take(max(1, min($max, 10)))->map(fn ($a) => [
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
                ])->values()->toArray();

                return [
                    'success' => true,
                    'message' => count($articles).' articles found.',
                    'data' => ['articles' => $articles, 'total' => $data['totalArticles'] ?? count($articles)],
                ];
            }

            return ['success' => false, 'message' => "GNews returned HTTP {$response->status}.", 'data' => null];
        } catch (\Throwable) {
            Log::warning('GNews article request failed securely.');

            return ['success' => false, 'message' => 'GNews could not be reached securely.', 'data' => null];
        }
    }

    /**
     * Allowed ISO 3166-1 alpha-2 codes, lowercased.
     *
     * GNews' /search endpoint accepts a single `country` value and returns
     * nothing at all when given a list, so the request stays unconstrained and
     * results are filtered on the `source.country` GNews reports instead. That
     * keeps multi-market support without voiding the query.
     *
     * @return array<int, string>
     */
    private function allowedCountries(?string $country): array
    {
        $raw = trim((string) ($country ?? config('gnews.default_country', '')));
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $code): string => strtolower(trim($code)),
            explode(',', $raw)
        ), static fn (string $code): bool => $code !== ''));
    }

}
