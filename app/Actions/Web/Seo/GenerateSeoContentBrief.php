<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo;

use App\Actions\Helpers\AI\Traits\WithAIGateway;
use App\Enums\Web\Seo\SeoContentBriefStateEnum;
use App\Models\Helpers\Language;
use App\Models\Web\SeoApiRequest;
use App\Models\Web\SeoContentBrief;
use App\Models\Web\SeoTrackedKeyword;
use App\Services\DataForSeo\DataForSeoClient;
use App\Services\DataForSeo\DataForSeoException;
use App\Services\Seo\ReadPage;
use App\Services\SeoApi\SeoApiBudget;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;
use Throwable;

/**
 * Writes the brief of one keyword: reads Google's top 10 for it (one live DataForSEO check), reads
 * those pages (title, headings, length, how they start), and has the AI gateway turn that, the
 * questions and related searches Google shows and our own page for the keyword into a brief. The
 * lengths and counts are measured here, not asked from the model. Guidance is written in English,
 * the copy to use (title, headings, questions, terms) in the keyword's language.
 */
class GenerateSeoContentBrief
{
    use AsAction;
    use WithAIGateway;

    public const string USER_AGENT = 'Mozilla/5.0 (compatible; AikuContentBriefBot/1.0)';

    public const int RESULTS = 10;

    private const int MIN_WORDS_READ = 80;

    private const int MAX_BYTES = 3_000_000;

    private const int HEADINGS_FOR_MODEL = 25;

    private const int TEXT_FOR_MODEL = 700;

    private const int SEARCH_CONSOLE_DAYS = 90;

    public string $jobQueue = 'long-low-priority';

    public int $jobTimeout = 300;

    public int $jobTries = 1;

    public function handle(SeoContentBrief $brief): SeoContentBrief
    {
        try {
            $serp    = $this->serp($brief);
            $pages   = $this->readPages($brief, $serp['organic']);
            $stats   = $this->stats($pages);
            $ourPage = $this->ourPage($brief) ?? $this->ourResult($brief, $pages);

            $brief->update([
                'pages'            => $pages->map(fn (array $page) => Arr::except($page, 'text'))->all(),
                'questions'        => $serp['questions'],
                'related_searches' => $serp['related_searches'],
                'stats'            => $stats,
                'our_page'         => $ourPage,
            ]);

            $brief->update([
                'brief'       => $this->write($brief, $pages, $serp, $stats, $ourPage),
                'model'       => $this->aiModel(GenerateSeoContentSuggestions::MODEL),
                'state'       => SeoContentBriefStateEnum::READY,
                'error'       => null,
                'finished_at' => now(),
            ]);
        } catch (Throwable $e) {
            $brief->update([
                'state'       => SeoContentBriefStateEnum::FAILED,
                'error'       => Str::limit($e->getMessage(), 500),
                'finished_at' => now(),
            ]);
        }

        return $brief;
    }

    /**
     * @return array{organic: array<int, array{position: int, url: string, domain: string, title: string|null, description: string|null}>, questions: array<int, string>, related_searches: array<int, string>}
     * @throws DataForSeoException
     */
    private function serp(SeoContentBrief $brief): array
    {
        $client = DataForSeoClient::make();

        if (!$client) {
            throw new RuntimeException(__('DataForSEO is not set up.'));
        }

        [$locationCode, $languageCode] = GetKeywordIdeas::make()->location($client, $brief->country_code, $brief->language_code);

        $result = Arr::first($client->live('serp/google/organic/live/advanced', [
            'keyword'       => $brief->keyword,
            'location_code' => $locationCode,
            'language_code' => $languageCode,
            'device'        => 'desktop',
            'depth'         => self::RESULTS,
        ], $brief->shop->website));

        $items = collect(Arr::get($result ?? [], 'items') ?? []);

        $organic = $items->where('type', 'organic')
            ->filter(fn (array $item) => Arr::get($item, 'url'))
            ->take(self::RESULTS)
            ->map(fn (array $item) => [
                'position'    => (int) Arr::get($item, 'rank_group'),
                'url'         => (string) $item['url'],
                'domain'      => StoreSerpResult::normaliseDomain(Arr::get($item, 'domain') ?: $item['url']),
                'title'       => Arr::get($item, 'title'),
                'description' => Arr::get($item, 'description'),
            ])
            ->values()
            ->all();

        if ($organic === []) {
            throw new RuntimeException(__('Google returned no results for this keyword.'));
        }

        return [
            'organic'          => $organic,
            'questions'        => $items->where('type', 'people_also_ask')->flatMap(fn (array $item) => collect(Arr::get($item, 'items') ?? [])->pluck('title'))->filter()->unique()->values()->all(),
            'related_searches' => $items->whereIn('type', ['related_searches', 'people_also_search'])->flatMap(fn (array $item) => Arr::get($item, 'items') ?? [])->filter(fn ($search) => is_string($search))->unique()->values()->all(),
        ];
    }

    /**
     * Fetches the pages side by side. A page that blocks robots, answers with something other than
     * HTML or has next to no text keeps its Google title and description and is marked as not read.
     *
     * @param  array<int, array{position: int, url: string, domain: string, title: string|null, description: string|null}>  $organic
     * @return Collection<int, array<string, mixed>>
     */
    private function readPages(SeoContentBrief $brief, array $organic): Collection
    {
        $ourDomain = (string) $brief->shop->website?->domain;
        $fetchable = array_filter($organic, fn (array $result) => $this->isPublicUrl($result['url']));

        $responses = $fetchable ? Http::pool(fn (Pool $pool) => array_map(
            fn (array $result) => $pool->as((string) $result['position'])
                ->withHeaders(['User-Agent' => self::USER_AGENT, 'Accept' => 'text/html'])
                ->withOptions(['allow_redirects' => ['max' => 3, 'protocols' => ['http', 'https']]])
                ->connectTimeout(5)
                ->timeout(12)
                ->get($result['url']),
            $fetchable
        )) : [];

        return collect($organic)->map(function (array $result) use ($responses, $ourDomain) {
            $response = $responses[(string) $result['position']] ?? null;
            $page     = $response instanceof Response && $response->successful() && str_contains(strtolower((string) $response->header('Content-Type')), 'html') && strlen($response->body()) <= self::MAX_BYTES
                ? rescue(fn () => ReadPage::parse($response->body()), null, false)
                : null;
            $isRead   = $page !== null && $page->wordCount >= self::MIN_WORDS_READ;

            return [
                ...$result,
                'is_ours'    => StoreSerpResult::isDomain($result['domain'], $ourDomain),
                'is_read'    => $isRead,
                'word_count' => $isRead ? $page->wordCount : null,
                'headings'   => $isRead ? $page->headings : [],
                'text'       => $isRead ? mb_substr($page->text, 0, self::TEXT_FOR_MODEL) : null,
            ];
        });
    }

    /**
     * Only http and https addresses that resolve to public hosts are fetched.
     */
    private function isPublicUrl(string $url): bool
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host   = (string) parse_url($url, PHP_URL_HOST);

        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            return false;
        }

        $addresses = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);

        return $addresses !== [] && collect($addresses)->every(fn (string $address) => filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false);
    }

    /**
     * Measured over the pages read that are not ours.
     *
     * @param  Collection<int, array<string, mixed>>  $pages
     * @return array{results: int, pages_read: int, median_words: int|null, min_words: int|null, max_words: int|null, median_headings: int|null}
     */
    private function stats(Collection $pages): array
    {
        $read  = $pages->where('is_read', true)->where('is_ours', false);
        $words = $read->pluck('word_count');

        return [
            'results'         => $pages->count(),
            'pages_read'      => $pages->where('is_read', true)->count(),
            'median_words'    => $words->isNotEmpty() ? (int) round($words->median()) : null,
            'min_words'       => $words->min(),
            'max_words'       => $words->max(),
            'median_headings' => $read->isNotEmpty() ? (int) round($read->map(fn (array $page) => collect($page['headings'])->where('level', '>', 1)->count())->median()) : null,
        ];
    }

    /**
     * The page of our website Google shows most for this exact search, from Search Console.
     *
     * @return array{url: string, webpage_id: int|null, clicks: int|null, impressions: int|null, position: float|null}|null
     */
    private function ourPage(SeoContentBrief $brief): ?array
    {
        $website = $brief->shop->website;

        if (!$website) {
            return null;
        }

        $page = DB::connection('aiku_no_sticky')->table('search_console_page_queries')
            ->where('website_id', $website->id)
            ->whereRaw('lower(query) = ?', [Str::lower($brief->keyword)])
            ->where('date', '>=', now()->subDays(self::SEARCH_CONSOLE_DAYS)->toDateString())
            ->groupBy('page_url', 'webpage_id')
            ->select('page_url', 'webpage_id')
            ->selectRaw('SUM(clicks) AS clicks')
            ->selectRaw('SUM(impressions) AS impressions')
            ->selectRaw('ROUND(SUM(position * impressions) / NULLIF(SUM(impressions), 0), 1) AS position')
            ->orderByDesc('impressions')
            ->first();

        return $page ? [
            'url'         => $page->page_url,
            'webpage_id'  => $page->webpage_id,
            'clicks'      => (int) $page->clicks,
            'impressions' => (int) $page->impressions,
            'position'    => $page->position !== null ? (float) $page->position : null,
        ] : null;
    }

    /**
     * Without Search Console data for the search: our page in the results just read, or the page the
     * rank tracking found for the keyword.
     *
     * @param  Collection<int, array<string, mixed>>  $pages
     * @return array{url: string, webpage_id: int|null, clicks: int|null, impressions: int|null, position: float|null}|null
     */
    private function ourResult(SeoContentBrief $brief, Collection $pages): ?array
    {
        $result = $pages->firstWhere('is_ours', true);

        if ($result) {
            return ['url' => $result['url'], 'webpage_id' => null, 'clicks' => null, 'impressions' => null, 'position' => (float) $result['position']];
        }

        $tracked = SeoTrackedKeyword::query()
            ->where('shop_id', $brief->shop_id)
            ->where('keyword', Str::lower($brief->keyword))
            ->where('country_code', $brief->country_code)
            ->whereNotNull('ranking_url')
            ->orderByDesc('last_checked_at')
            ->first();

        return $tracked ? ['url' => $tracked->ranking_url, 'webpage_id' => $tracked->ranking_webpage_id, 'clicks' => null, 'impressions' => null, 'position' => (float) $tracked->position] : null;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $pages
     * @return array<string, mixed>
     */
    private function write(SeoContentBrief $brief, Collection $pages, array $serp, array $stats, ?array $ourPage, int $attempt = 1): array
    {
        if (!$this->aiApiKey()) {
            throw new RuntimeException(__('The AI gateway is not set up. Add OPENROUTER_API_KEY to the environment.'));
        }

        if (SeoApiBudget::isReached()) {
            throw new RuntimeException(__('The SEO API budget for this month (:budget USD) has been reached.', ['budget' => SeoApiBudget::monthlyBudget()]));
        }

        $startedAt = hrtime(true);
        $provider  = $this->usesOpenRouter() ? 'openrouter' : 'openai';

        try {
            $response = $this->aiRequest()
                ->timeout(120)
                ->post('chat/completions', [
                    'model'           => $this->aiModel(GenerateSeoContentSuggestions::MODEL),
                    'messages'        => [
                        ['role' => 'system', 'content' => $this->instructions($brief)],
                        ['role' => 'user', 'content' => $this->context($brief, $pages, $serp, $stats, $ourPage)],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'max_tokens'      => 3000,
                ])
                ->throw();
        } catch (Throwable $e) {
            $this->log($brief, $provider, false, null, $startedAt, $e->getMessage());

            throw new RuntimeException(__('The AI gateway did not answer: :error', ['error' => Str::limit($e->getMessage(), 200)]));
        }

        $content  = (string) $response->json('choices.0.message.content');
        $written  = preg_match('/\{.*\}/s', $content, $match) ? json_decode($match[0], true) : null;
        $isUsable = is_array($written) && is_array($written['outline'] ?? null) && $written['outline'] !== [];

        $this->log($brief, $provider, $isUsable, $this->aiUsageCost($response->json('usage') ?? []), $startedAt, $isUsable ? null : 'No usable JSON in the answer: '.Str::limit($content, 300));

        if (!$isUsable) {
            if ($attempt < 2) {
                return $this->write($brief, $pages, $serp, $stats, $ourPage, $attempt + 1);
            }

            throw new RuntimeException(__('The AI gateway answered without a usable brief. Try again.'));
        }

        $strings = fn (mixed $list) => collect(is_array($list) ? $list : [])->filter(fn ($value) => is_string($value) && trim($value) !== '')->map(fn (string $value) => Str::squish($value))->values()->all();
        $string  = fn (mixed $value) => is_string($value) && trim($value) !== '' ? Str::squish($value) : null;

        return [
            'search_intent'    => $string($written['search_intent'] ?? null),
            'content_type'     => $string($written['content_type'] ?? null),
            'approach'         => $string($written['approach'] ?? null),
            'title'            => $string($written['title'] ?? null),
            'meta_description' => $string($written['meta_description'] ?? null),
            'h1'               => $string($written['h1'] ?? null),
            'outline'          => collect($written['outline'])
                ->filter(fn ($section) => is_array($section) && $string($section['heading'] ?? null))
                ->map(fn (array $section) => [
                    'heading' => $string($section['heading']),
                    'level'   => (int) ($section['level'] ?? 2) === 3 ? 3 : 2,
                    'notes'   => $string($section['notes'] ?? null),
                ])
                ->values()
                ->all(),
            'questions'        => $strings($written['questions'] ?? null),
            'terms'            => $strings($written['terms'] ?? null),
            'stand_out'        => $strings($written['stand_out'] ?? null),
        ];
    }

    private function instructions(SeoContentBrief $brief): string
    {
        $language = Language::where('code', $brief->language_code)->value('name') ?? $brief->language_code;

        return "You prepare a content brief for a writer of an online wholesale shop. The writer will create or improve one page so that it deserves to rank in Google for the keyword given.\n\n"
            ."You get the pages Google ranks now, with their headings, length and opening text, the questions and related searches Google shows, and what our own website has for the keyword.\n\n"
            ."Return only a JSON object with these keys:\n"
            ."- \"search_intent\": one or two sentences in English on what the searcher wants.\n"
            ."- \"content_type\": in English, the kind of page that ranks (for example category page, product page, buying guide, how-to article, comparison).\n"
            ."- \"approach\": in English, whether to improve our existing page or write a new one, and what kind, in one or two sentences. Base it on \"Our website\" in the input.\n"
            ."- \"title\": a page title in $language, at most 60 characters.\n"
            ."- \"meta_description\": a meta description in $language, 120 to 155 characters.\n"
            ."- \"h1\": the main heading in $language.\n"
            ."- \"outline\": 5 to 12 sections in reading order, each {\"heading\": in $language, \"level\": 2 or 3, \"notes\": one or two sentences in English on what to cover}.\n"
            ."- \"questions\": up to 8 questions in $language the page should answer, taken from Google's questions and the ranking pages.\n"
            ."- \"terms\": up to 20 words or short phrases in $language that the ranking pages use and the page should use naturally.\n"
            ."- \"stand_out\": up to 5 points in English on what the ranking pages leave out or do poorly, where our page can be more useful.\n\n"
            .'Use only what the input shows: no invented facts, prices, statistics, brands or product claims. Numbers and claims about our range (how many products, scents, sizes, delivery, awards) may only come from our own page in the input, never from another website; without one, leave them out of the title, meta description and H1. Do not copy headings word for word from one page. No emoji, no exclamation marks, no capital letters for emphasis.';
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $pages
     */
    private function context(SeoContentBrief $brief, Collection $pages, array $serp, array $stats, ?array $ourPage): string
    {
        $website = $brief->shop->website;

        $lines = [
            'Keyword: '.$brief->keyword,
            'Market: '.$brief->country_code.', language '.$brief->language_code,
            'Our website: '.($website ? $website->name.' ('.$website->domain.'), a wholesale shop' : 'unknown'),
            $ourPage
                ? "Our page for this search: {$ourPage['url']} (position ".($ourPage['position'] ?? '-').($ourPage['impressions'] !== null ? ", {$ourPage['impressions']} impressions in ".self::SEARCH_CONSOLE_DAYS.' days' : '').')'
                : 'Our page for this search: none found in Google Search Console, in the results below or in our rank tracking',
            $stats['median_words'] ? "Length of the ranking pages read: median {$stats['median_words']} words, from {$stats['min_words']} to {$stats['max_words']}" : null,
            $serp['questions'] ? 'Questions Google shows: '.implode(' | ', $serp['questions']) : null,
            $serp['related_searches'] ? 'Related searches: '.implode(' | ', array_slice($serp['related_searches'], 0, 12)) : null,
            '',
            'Pages ranking now:',
        ];

        foreach ($pages as $page) {
            $lines[] = '';
            $lines[] = "#{$page['position']} {$page['domain']}".($page['is_ours'] ? ' (our website)' : '');
            $lines[] = 'Title: '.($page['title'] ?? '-');
            $lines[] = 'Google snippet: '.($page['description'] ?? '-');

            if (!$page['is_read']) {
                $lines[] = 'Page content: could not be read';

                continue;
            }

            $lines[] = "Words: {$page['word_count']}";
            $lines[] = 'Headings: '.collect($page['headings'])->take(self::HEADINGS_FOR_MODEL)->map(fn (array $heading) => "H{$heading['level']} {$heading['text']}")->implode(' | ');
            $lines[] = 'Opening text: '.$page['text'];
        }

        return implode("\n", array_filter($lines, fn ($line) => $line !== null));
    }

    private function log(SeoContentBrief $brief, string $provider, bool $isSuccess, ?float $cost, int $startedAt, ?string $error = null): void
    {
        SeoApiRequest::create([
            'provider'    => $provider,
            'endpoint'    => 'content_brief',
            'website_id'  => $brief->shop->website?->id,
            'is_success'  => $isSuccess,
            'rows'        => 1,
            'duration_ms' => intdiv(hrtime(true) - $startedAt, 1_000_000),
            'cost'        => $cost,
            'error'       => $error ? Str::limit($error, 2000) : null,
        ]);
    }
}
