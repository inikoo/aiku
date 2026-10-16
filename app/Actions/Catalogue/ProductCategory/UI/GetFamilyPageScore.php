<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\UI;

use App\Actions\Traits\Authorisations\WithShopPpcPermissions;
use App\Enums\Web\Crawl\CrawlIssueSeverityEnum;
use App\Enums\Web\Crawl\CrawlIssueTypeEnum;
use App\Enums\Web\Crawl\CrawlStateEnum;
use App\Enums\Web\Crawl\CrawlTypeEnum;
use App\Enums\Web\Seo\SeoContentSuggestionStateEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Web\CrawlIssue;
use App\Models\Web\CrawlPage;
use App\Models\Web\SeoContentSuggestion;
use App\Models\Web\Webpage;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsObject;

class GetFamilyPageScore
{
    use AsObject;
    use WithShopPpcPermissions;

    /**
     * How far the family webpage is through its SEO workflow and which of its checks pass. Checks
     * read from the site audit are null until an audit has crawled the page.
     *
     * @return null|array{steps: array<int, array{key: string, label: string, owner: string, is_done: bool}>, score: int, passed: int, total: int, last_crawl_at: ?string, findings: ?array<int, array{type: string, label: string, description: string, is_error: bool}>, areas: array<int, array{key: string, label: string, checks: array<int, array{key: string, label: string, is_passed: ?bool}>}>, crawl_route: ?array, issues_route: array}
     */
    public function handle(ProductCategory $family): ?array
    {
        $webpage = $family->webpage;

        if (!$webpage) {
            return null;
        }

        $crawlPage             = $this->latestCrawlPage($webpage);
        $issueTypes = $crawlPage?->issues->map(fn (CrawlIssue $issue) => $issue->type);
        $isClearOf  = fn (array $types): ?bool => $issueTypes ? !$issueTypes->contains(fn (CrawlIssueTypeEnum $type) => in_array($type, $types, true)) : null;
        $visibility = $webpage->searchEngineVisibility();
        $isLive     = $webpage->state === WebpageStateEnum::LIVE;

        $areas = collect([
            'content'   => [__('Content'), [
                'meta_title'        => [__('Meta title'), $this->hasText($webpage->title) && $isClearOf([CrawlIssueTypeEnum::MISSING_TITLE, CrawlIssueTypeEnum::TITLE_TOO_LONG, CrawlIssueTypeEnum::TITLE_TOO_SHORT, CrawlIssueTypeEnum::DUPLICATE_TITLE]) !== false],
                'meta_description'  => [__('Meta description'), $this->hasText($webpage->description) && $isClearOf([CrawlIssueTypeEnum::MISSING_META_DESCRIPTION, CrawlIssueTypeEnum::META_DESCRIPTION_TOO_LONG, CrawlIssueTypeEnum::META_DESCRIPTION_TOO_SHORT, CrawlIssueTypeEnum::DUPLICATE_META_DESCRIPTION]) !== false],
                'top_description'   => [__('Top description'), $this->hasText($family->description)],
                'extra_description' => [__('Extra description'), $this->hasText($family->description_extra)],
                'faq'               => [__('FAQ'), collect($family->faq)->contains(fn ($faq) => is_array($faq) && $this->hasText($faq['question'] ?? null))],
                'price'             => [__('Price'), Product::where('family_id', $family->id)->where('is_for_sale', true)->where('price', '>', 0)->exists()],
            ]],
            'structure' => [__('Structure'), [
                'h1'               => [__('H1'), $isClearOf([CrawlIssueTypeEnum::MISSING_H1, CrawlIssueTypeEnum::MULTIPLE_H1])],
                'url'              => [__('URL'), (bool)preg_match('/^[a-z0-9]+(?:[-\/][a-z0-9]+)*$/', $webpage->url)],
                'breadcrumb_label' => [__('Breadcrumb label'), $this->hasText($webpage->breadcrumb_label)],
                'canonical_url'    => [__('Canonical URL'), $this->hasText($webpage->canonical_url) && $isClearOf([CrawlIssueTypeEnum::MISSING_CANONICAL, CrawlIssueTypeEnum::CANONICAL_TO_BROKEN]) !== false],
                'structured_data'  => [__('Structured data'), !empty($webpage->structured_data) || !empty(Arr::get($webpage->seo_data, 'structured_data'))],
                'sitemap'          => [__('Sitemap'), $isClearOf([CrawlIssueTypeEnum::NOT_IN_SITEMAP, CrawlIssueTypeEnum::NOINDEX_IN_SITEMAP])],
            ]],
            'links'     => [__('Links & images'), [
                'alt_image'    => [__('Alt image'), $isClearOf([CrawlIssueTypeEnum::IMAGES_WITHOUT_ALT])],
                'broken_links' => [__('Broken links'), $crawlPage ? !$this->linksToBrokenPages($crawlPage) : null],
                'image'        => [__('Image'), (bool)$family->image_id],
            ]],
            'indexing'  => [__('Indexing'), [
                'index_page'  => [__('Index page'), $visibility['index_page']],
                'follow_link' => [__('Follow link'), $visibility['follow_link']],
                'page_status' => [__('Page status'), $isLive],
            ]],
            'performance' => [__('Performance'), [
                'page_speed' => [__('Page speed'), $isClearOf([CrawlIssueTypeEnum::SLOW_RESPONSE])],
            ]],
        ])->map(fn (array $area, string $key) => [
            'key'    => $key,
            'label'  => $area[0],
            'checks' => collect($area[1])->map(fn (array $check, string $checkKey) => [
                'key'       => $checkKey,
                'label'     => $check[0],
                'is_passed' => $check[1],
            ])->values()->all(),
        ])->values();

        $checks = $areas->flatMap(fn (array $area) => $area['checks']);
        $passed = $checks->where('is_passed', true)->count();

        $suggestionStates = SeoContentSuggestion::where('webpage_id', $webpage->id)->distinct()->pluck('state');
        $isReviewed       = $suggestionStates->isNotEmpty();
        $website          = $webpage->website;
        $shop             = $family->shop;
        $user             = request()->user();

        return [
            'steps'         => $this->steps(
                isCreated: true,
                isPublished: $isLive,
                isReviewed: $isReviewed,
                isImplemented: $isReviewed && !$suggestionStates->contains(fn (SeoContentSuggestionStateEnum $state) => in_array($state, [SeoContentSuggestionStateEnum::PENDING, SeoContentSuggestionStateEnum::IMPLEMENTED], true))
            ),
            'score'         => (int)round($passed * 100 / $checks->count()),
            'passed'        => $passed,
            'total'         => $checks->count(),
            'last_crawl_at' => $crawlPage?->crawl->end_at?->toIso8601String(),
            'areas'         => $areas->all(),
            'findings'      => $crawlPage?->issues->map(fn (CrawlIssue $issue) => [
                'type'        => $issue->type->value,
                'label'       => CrawlIssueTypeEnum::labels()[$issue->type->value],
                'description' => CrawlIssueTypeEnum::descriptions()[$issue->type->value],
                'is_error'    => $issue->type->severity() === CrawlIssueSeverityEnum::ERROR,
            ])->values()->all(),
            'crawl_route'   => $user && $this->canEditSeo($user, $shop) ? [
                'name'       => 'grp.models.website.site_audit.store',
                'parameters' => [$website->id],
                'method'     => 'post',
            ] : null,
            'issues_route'  => [
                'name'       => 'grp.org.shops.show.web.webpages.show',
                'parameters' => [$shop->organisation->slug, $shop->slug, $website->slug, $webpage->slug],
            ],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, owner: string, is_done: bool}>
     */
    public function steps(bool $isCreated, bool $isPublished, bool $isReviewed, bool $isImplemented): array
    {
        return [
            ['key' => 'created', 'label' => __('Page created'), 'owner' => __('Webmaster'), 'is_done' => $isCreated],
            ['key' => 'published', 'label' => __('Published'), 'owner' => __('Webmaster'), 'is_done' => $isPublished],
            ['key' => 'seo_review', 'label' => __('SEO review & suggestions'), 'owner' => __('SEO team'), 'is_done' => $isReviewed],
            ['key' => 'implemented', 'label' => __('Suggestions implemented'), 'owner' => __('Webmaster'), 'is_done' => $isImplemented],
        ];
    }

    private function latestCrawlPage(Webpage $webpage): ?CrawlPage
    {
        return CrawlPage::where('webpage_id', $webpage->id)
            ->whereHas('crawl', fn ($query) => $query->where('type', CrawlTypeEnum::AUDIT)->where('state', CrawlStateEnum::FINISH))
            ->with(['crawl', 'issues'])
            ->orderByDesc('crawl_id')
            ->orderByRaw('status_code = 200 desc nulls last')
            ->first();
    }

    /**
     * ponytail: reads linked_from, which the audit caps at 10 linking pages per broken URL, so a
     * broken page linked from many others may not name this one. Store the outlinks per page if that matters.
     */
    private function linksToBrokenPages(CrawlPage $crawlPage): bool
    {
        return CrawlIssue::where('crawl_id', $crawlPage->crawl_id)
            ->whereIn('type', [CrawlIssueTypeEnum::HTTP_4XX, CrawlIssueTypeEnum::HTTP_5XX])
            ->whereJsonContains('details->linked_from', $crawlPage->url)
            ->exists();
    }

    private function hasText(?string $value): bool
    {
        return trim(html_entity_decode(strip_tags($value ?? ''))) !== '';
    }
}
