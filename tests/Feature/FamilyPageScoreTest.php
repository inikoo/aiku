<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Catalogue\ProductCategory\StoreProductCategoryWebpage;
use App\Actions\Catalogue\ProductCategory\UI\GetFamilyPageScore;
use App\Actions\Web\Crawl\AuditWebsite;
use App\Enums\Web\Crawl\CrawlIssueTypeEnum;
use App\Enums\Web\Crawl\CrawlStateEnum;
use App\Enums\Web\Crawl\CrawlTriggerEnum;
use App\Enums\Web\Seo\SeoContentSuggestionStateEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Models\Web\CrawlIssue;
use App\Models\Web\CrawlPage;
use App\Models\Web\SeoContentSuggestion;

beforeEach(function () {
    loadDB();
    $this->organisation = createOrganisation();
    $this->shop         = createShop($this->organisation)[2];
    $this->website      = createWebsite($this->shop);

    [, $product] = createProduct($this->shop);
    $product->updateQuietly(['is_for_sale' => true, 'price' => 10]);

    $this->family = $product->family;
    $this->family->updateQuietly([
        'description'       => '<p>Top description</p>',
        'description_extra' => '<p> </p>',
        'faq'               => [['question' => 'Why?', 'answer' => 'Because']],
        'image_id'          => null,
    ]);

    $this->webpage = $this->family->webpage ?? StoreProductCategoryWebpage::make()->action($this->family);
    $this->webpage->updateQuietly([
        'title'            => 'Family title',
        'description'      => 'Family description',
        'breadcrumb_label' => 'Family',
        'canonical_url'    => 'https://ecom.test/page-score-family',
        'url'              => 'page-score-family',
        'state'            => WebpageStateEnum::LIVE,
        'index_page'       => true,
        'follow_link'      => true,
        'structured_data'  => null,
        'seo_data'         => [],
    ]);

    CrawlPage::where('webpage_id', $this->webpage->id)->delete();
    SeoContentSuggestion::where('webpage_id', $this->webpage->id)->delete();
});

function pageScoreChecks(array $pageScore): array
{
    return collect($pageScore['areas'])->flatMap(fn (array $area) => $area['checks'])->pluck('is_passed', 'key')->all();
}

test('checks read from the site audit are not checked before the page is crawled', function () {
    SeoContentSuggestion::create([
        'website_id'    => $this->webpage->website_id,
        'webpage_id'    => $this->webpage->id,
        'field'         => 'title',
        'current_value' => 'Family title',
        'suggestion'    => 'Better family title',
        'state'         => SeoContentSuggestionStateEnum::PENDING,
    ]);

    $pageScore = GetFamilyPageScore::run($this->family->fresh());
    $checks    = pageScoreChecks($pageScore);

    expect($checks)->toHaveCount(19)
        ->and(array_keys(array_filter($checks, fn ($isPassed) => $isPassed === null)))->toBe(['h1', 'sitemap', 'alt_image', 'broken_links', 'page_speed'])
        ->and(array_keys(array_filter($checks, fn ($isPassed) => $isPassed === false)))->toBe(['extra_description', 'structured_data', 'image'])
        ->and($pageScore['passed'])->toBe(11)
        ->and($pageScore['score'])->toBe(58)
        ->and($pageScore['last_crawl_at'])->toBeNull()
        ->and(collect($pageScore['steps'])->pluck('is_done', 'key')->all())->toBe([
            'created'     => true,
            'published'   => true,
            'seo_review'  => true,
            'implemented' => false,
        ]);
});

test('the latest finished site audit fails the checks it found issues for, broken links included', function () {
    $crawl = AuditWebsite::startFor($this->website, CrawlTriggerEnum::USER);
    $crawl->update(['state' => CrawlStateEnum::FINISH, 'end_at' => now()]);

    $pageUrl   = 'https://ecom.test/page-score-family';
    $crawlPage = CrawlPage::create([
        'crawl_id'      => $crawl->id,
        'webpage_id'    => $this->webpage->id,
        'url'           => $pageUrl,
        'url_hash'      => md5($pageUrl),
        'status_code'   => 200,
        'is_in_sitemap' => true,
        'is_indexable'  => true,
    ]);
    $brokenPage = CrawlPage::create([
        'crawl_id'    => $crawl->id,
        'url'         => 'https://ecom.test/gone',
        'url_hash'    => md5('https://ecom.test/gone'),
        'status_code' => 404,
    ]);

    CrawlIssue::create(['crawl_id' => $crawl->id, 'crawl_page_id' => $crawlPage->id, 'type' => CrawlIssueTypeEnum::MISSING_H1, 'severity' => CrawlIssueTypeEnum::MISSING_H1->severity()]);
    CrawlIssue::create(['crawl_id' => $crawl->id, 'crawl_page_id' => $brokenPage->id, 'type' => CrawlIssueTypeEnum::HTTP_4XX, 'severity' => CrawlIssueTypeEnum::HTTP_4XX->severity(), 'details' => ['linked_from' => [$pageUrl]]]);

    $pageScore = GetFamilyPageScore::run($this->family->fresh());
    $checks    = pageScoreChecks($pageScore);

    expect(array_filter($checks, fn ($isPassed) => $isPassed === null))->toBeEmpty()
        ->and(array_keys(array_filter($checks, fn ($isPassed) => $isPassed === false)))->toBe(['extra_description', 'h1', 'structured_data', 'broken_links', 'image'])
        ->and($pageScore['passed'])->toBe(14)
        ->and($pageScore['score'])->toBe(74)
        ->and($pageScore['last_crawl_at'])->not->toBeNull()
        ->and($pageScore['steps'][2]['is_done'])->toBeFalse();
});
