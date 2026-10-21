<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 07 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo;

use App\Enums\Web\Seo\SeoResearchScopeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Web\SeoKeyword;
use App\Services\DataForSeo\DataForSeoClient;
use App\Services\DataForSeo\DataForSeoException;
use App\Services\DataForSeo\DataForSeoLocations;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsObject;

class GetKeywordIdeas
{
    use AsObject;

    public const int MAX_IDEAS = 300;

    public const int MAX_SEED_KEYWORDS = 5;

    private const int CACHE_HOURS = 24;

    /**
     * Each seed keyword brings its own figures and the keywords that contain it, sharing the
     * MAX_IDEAS rows between the seeds so the cost of a search stays the same; a URL brings the
     * keywords its root domain, subdomain, subfolder or exact page ranks for in Google, by scope.
     *
     * @param  array<int, string>  $seedKeywords
     * @return array{ideas: array<int, array>}
     * @throws ValidationException
     */
    public function handle(Shop $shop, array $seedKeywords, ?string $url, string $countryCode, string $languageCode, ?SeoResearchScopeEnum $scope = null): array
    {
        $seedKeywords = array_values(array_unique(array_filter(array_map(fn ($keyword) => Str::lower(trim($keyword)), $seedKeywords))));
        $url          = $url ? trim($url) : null;
        $countryCode  = strtoupper($countryCode);
        $languageCode = strtolower($languageCode);
        $target       = $url ? self::rankedKeywordsTarget($url, $scope ?? SeoResearchScopeEnum::guess($url)) : null;

        if ($seedKeywords === [] && !$url) {
            throw ValidationException::withMessages(['seed' => __('Enter at least one keyword or a URL.')]);
        }

        if (count($seedKeywords) > self::MAX_SEED_KEYWORDS) {
            throw ValidationException::withMessages(['seed' => __('Search up to :max keywords at a time.', ['max' => self::MAX_SEED_KEYWORDS])]);
        }

        $client = DataForSeoClient::make();

        if (!$client) {
            throw ValidationException::withMessages(['seed' => __('DataForSEO is not set up. Add DATAFORSEO_LOGIN and DATAFORSEO_PASSWORD to the environment.')]);
        }

        $cacheKey = 'seo:keyword_suggestions:'.md5(json_encode([$seedKeywords, $target, $countryCode, $languageCode]));

        try {
            $ideas = Cache::get($cacheKey);

            if ($ideas === null) {
                $ideas = $this->fetchIdeas($client, $shop, $seedKeywords, $target, ...$this->location($client, $countryCode, $languageCode));

                $this->store($shop, $ideas, $countryCode, $languageCode);

                Cache::put($cacheKey, $ideas, now()->addHours(self::CACHE_HOURS));
            }
        } catch (DataForSeoException $e) {
            throw ValidationException::withMessages(['seed' => $e->getMessage()]);
        }

        return ['ideas' => $ideas];
    }

    /**
     * @return array{0: int, 1: string}
     * @throws ValidationException
     * @throws DataForSeoException
     */
    public function location(DataForSeoClient $client, string $countryCode, string $languageCode): array
    {
        $location = DataForSeoLocations::forCountry($client, $countryCode);

        if (!$location) {
            throw ValidationException::withMessages(['country_code' => __('DataForSEO has no keyword data for this country.')]);
        }

        $languageCode = str_replace('_', '-', $languageCode);

        foreach ([$languageCode, Str::before($languageCode, '-')] as $candidate) {
            if (in_array($candidate, $location['languages'], true)) {
                return [$location['location_code'], $candidate];
            }
        }

        throw ValidationException::withMessages(['language_code' => __('DataForSEO has no keyword data in this language for this country. It has: :languages.', ['languages' => implode(', ', $location['languages'])])]);
    }

    /**
     * The ranked_keywords target for a URL: the domain with or without its subdomains, the page
     * itself, or the domain narrowed to the pages under one folder of it (with or without www).
     *
     * @return array{target: string, include_subdomains?: bool, filters?: array}
     * @throws ValidationException
     */
    public static function rankedKeywordsTarget(string $url, SeoResearchScopeEnum $scope): array
    {
        $absoluteUrl = Str::contains($url, '://') ? $url : "https://$url";
        $host        = Str::lower((string) parse_url($absoluteUrl, PHP_URL_HOST));
        $domain      = StoreSerpResult::normaliseDomain($host);

        if ($domain === '') {
            throw ValidationException::withMessages(['url' => __('Enter a valid domain or URL.')]);
        }

        return match ($scope) {
            SeoResearchScopeEnum::ROOT_DOMAIN => ['target' => self::rootDomain($domain), 'include_subdomains' => true],
            SeoResearchScopeEnum::SUBDOMAIN   => ['target' => $domain, 'include_subdomains' => false],
            SeoResearchScopeEnum::EXACT_URL   => ['target' => $absoluteUrl],
            SeoResearchScopeEnum::SUBFOLDER   => self::subfolderTarget($host, $domain, (string) parse_url($absoluteUrl, PHP_URL_PATH)),
        };
    }

    /**
     * The registered domain of a host without a public suffix list: the last two labels, or the
     * last three under a second level suffix such as co.uk or com.au.
     */
    public static function rootDomain(string $domain): string
    {
        $labels         = explode('.', $domain);
        $underSecondLevelSuffix = count($labels) > 2 && in_array($labels[count($labels) - 2], ['co', 'com', 'net', 'org', 'gov', 'edu', 'ac', 'ltd', 'plc', 'or', 'ne', 'go'], true);

        return implode('.', array_slice($labels, $underSecondLevelSuffix ? -3 : -2));
    }

    /**
     * @return array{target: string, include_subdomains: bool, filters: array}
     */
    private static function subfolderTarget(string $host, string $domain, string $path): array
    {
        $folder  = trim($path, '/') === '' ? '/' : '/'.trim($path, '/').'/';
        $hosts   = in_array($host, [$domain, "www.$domain"], true) ? [$domain, "www.$domain"] : [$host];
        $filters = array_map(fn (string $folderHost) => ['ranked_serp_element.serp_item.url', 'like', "%://$folderHost$folder%"], $hosts);

        return [
            'target'             => $domain,
            'include_subdomains' => true,
            'filters'            => count($filters) === 1 ? $filters[0] : [$filters[0], 'or', $filters[1]],
        ];
    }

    /**
     * @param  array<int, string>  $seedKeywords
     * @return array<int, array>
     * @throws DataForSeoException
     */
    private function fetchIdeas(DataForSeoClient $client, Shop $shop, array $seedKeywords, ?array $target, int $locationCode, string $languageCode): array
    {
        $location = ['location_code' => $locationCode, 'language_code' => $languageCode];
        $items    = [];

        foreach ($seedKeywords as $seedKeyword) {
            $suggestions = Arr::first($client->live('dataforseo_labs/google/keyword_suggestions/live', [
                ...$location,
                'keyword'              => $seedKeyword,
                'include_seed_keyword' => true,
                'include_serp_info'    => true,
                'limit'                => intdiv(self::MAX_IDEAS, count($seedKeywords)),
                'order_by'             => ['keyword_info.search_volume,desc'],
            ], $shop->website)) ?? [];

            if (Arr::get($suggestions, 'seed_keyword_data')) {
                $items[] = [...$suggestions['seed_keyword_data'], 'keyword' => $seedKeyword];
            }

            array_push($items, ...Arr::get($suggestions, 'items') ?? []);
        }

        if ($target) {
            $rankedKeywords = Arr::get($client->live('dataforseo_labs/google/ranked_keywords/live', [...$location, ...$target, 'include_serp_info' => true, 'limit' => self::MAX_IDEAS, 'order_by' => ['keyword_data.keyword_info.search_volume,desc']], $shop->website), '0.items') ?? [];

            array_push($items, ...array_map(fn (array $item) => Arr::get($item, 'keyword_data', []), $rankedKeywords));
        }

        return collect($items)
            ->map(fn (array $item) => self::idea($item))
            ->filter(fn (array $idea) => $idea['keyword'] !== '' && mb_strlen($idea['keyword']) <= 255)
            ->unique('keyword')
            ->sortBy(fn (array $idea) => [in_array($idea['keyword'], $seedKeywords, true) ? 0 : 1, -($idea['avg_monthly_searches'] ?? -1)])
            ->take(self::MAX_IDEAS)
            ->values()
            ->all();
    }

    /**
     * One DataForSEO Labs keyword item, as stored in `seo_keywords` and shown on the Research tab.
     */
    public static function idea(array $item): array
    {
        $competition = Arr::get($item, 'keyword_info.competition');

        return [
            'keyword'              => Str::lower((string) Arr::get($item, 'keyword')),
            'avg_monthly_searches' => Arr::get($item, 'keyword_info.search_volume'),
            'monthly_searches'     => collect(Arr::get($item, 'keyword_info.monthly_searches') ?? [])
                ->map(fn (array $month) => [
                    'year'     => (int) Arr::get($month, 'year'),
                    'month'    => (int) Arr::get($month, 'month'),
                    'searches' => (int) Arr::get($month, 'search_volume', 0),
                ])
                ->sortBy(fn (array $month) => $month['year'] * 100 + $month['month'])
                ->values()
                ->all(),
            'competition'          => Arr::get($item, 'keyword_info.competition_level'),
            'competition_index'    => is_numeric($competition) ? (int) round($competition * 100) : null,
            'cpc'                  => Arr::get($item, 'keyword_info.cpc'),
            'low_top_of_page_bid'  => Arr::get($item, 'keyword_info.low_top_of_page_bid'),
            'high_top_of_page_bid' => Arr::get($item, 'keyword_info.high_top_of_page_bid'),
            'keyword_difficulty'   => Arr::get($item, 'keyword_properties.keyword_difficulty') ?: null,
            'intent'               => Arr::get($item, 'search_intent_info.main_intent'),
            'secondary_intents'    => array_values(array_filter(Arr::wrap(Arr::get($item, 'search_intent_info.foreign_intent')))),
            'serp_features'        => array_values(array_diff(array_filter(Arr::get($item, 'serp_info.serp_item_types') ?? []), ['organic'])),
            'serp_updated_at'      => ($serpUpdatedAt = Arr::get($item, 'serp_info.last_updated_time')) ? Carbon::parse($serpUpdatedAt)->toDateString() : null,
        ];
    }

    public function store(Shop $shop, array $ideas, string $countryCode, string $languageCode): void
    {
        $now = now();

        foreach (array_chunk($ideas, 500) as $chunk) {
            SeoKeyword::upsert(
                array_map(fn (array $idea) => [
                    'shop_id'              => $shop->id,
                    'keyword'              => $idea['keyword'],
                    'country_code'         => $countryCode,
                    'language_code'        => $languageCode,
                    'avg_monthly_searches' => $idea['avg_monthly_searches'],
                    'monthly_searches'     => json_encode($idea['monthly_searches']),
                    'competition'          => $idea['competition'],
                    'competition_index'    => $idea['competition_index'],
                    'cpc'                  => $idea['cpc'],
                    'low_top_of_page_bid'  => $idea['low_top_of_page_bid'],
                    'high_top_of_page_bid' => $idea['high_top_of_page_bid'],
                    'keyword_difficulty'   => $idea['keyword_difficulty'],
                    'intent'               => $idea['intent'],
                    'secondary_intents'    => json_encode($idea['secondary_intents']),
                    'intent_source'        => $idea['intent'] ? SeoKeyword::SOURCE_DATAFORSEO_LABS : null,
                    'source'               => SeoKeyword::SOURCE_DATAFORSEO_LABS,
                    'fetched_at'           => $now,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ], $chunk),
                ['shop_id', 'keyword', 'country_code', 'language_code'],
                ['avg_monthly_searches', 'monthly_searches', 'competition', 'competition_index', 'cpc', 'low_top_of_page_bid', 'high_top_of_page_bid', 'keyword_difficulty', 'intent', 'secondary_intents', 'intent_source', 'source', 'fetched_at', 'updated_at']
            );
        }
    }
}
