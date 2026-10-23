<?php

use App\Actions\Iris\Blog\IndexIrisBlogs;
use App\Actions\Web\Webpage\ProcessUpdateWebpageUrl;
use App\Actions\Web\Webpage\StoreWebpage;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageSubTypeEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Models\Web\Redirect;
use App\Models\Web\Webpage;
use App\Models\Web\Website;

beforeEach(function () {
    loadDB();
    $this->organisation = createOrganisation();
    $this->shop         = createShop()[2];
    $this->website      = createWebsite($this->shop);
});

function createBlogWebpage(Website $website, string $title, array $attributes): Webpage
{
    $webpage = StoreWebpage::make()->action($website, array_merge(
        Webpage::factory()->definition(),
        [
            'title'    => $title,
            'type'     => WebpageTypeEnum::BLOG->value,
            'sub_type' => WebpageSubTypeEnum::BLOG->value,
        ]
    ));

    $webpage->updateQuietly(array_merge(['state' => WebpageStateEnum::LIVE], $attributes));

    return $webpage->refresh();
}

function publishedLayoutWithDate(?string $publishedDate): array
{
    return [
        'web_blocks' => [
            [
                'web_block' => [
                    'layout' => [
                        'data' => [
                            'fieldValue' => [
                                'published_date' => $publishedDate,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

test('iris blogs ascending sort lists the oldest published layout date first', function () {
    $oldest = createBlogWebpage($this->website, 'Oldest published blog', [
        'live_at'          => '2026-03-01 10:00:00',
        'published_layout' => publishedLayoutWithDate('2012-08-31T14:01:00.004Z'),
    ]);

    $newest = createBlogWebpage($this->website, 'Newest published blog', [
        'live_at'          => '2026-01-01 10:00:00',
        'published_layout' => publishedLayoutWithDate('2025-01-10T15:00:00.004Z'),
    ]);

    $middle = createBlogWebpage($this->website, 'Middle published blog', [
        'live_at'          => '2026-02-01 10:00:00',
        'published_layout' => publishedLayoutWithDate('2021-09-17T12:09:00.001Z'),
    ]);

    request()->query->set('sort', 'last_published_at');
    $ascending = IndexIrisBlogs::make()->handle($this->website)->pluck('id')->all();

    request()->query->set('sort', '-last_published_at');
    $descending = IndexIrisBlogs::make()->handle($this->website)->pluck('id')->all();

    expect($ascending)->toBe([$oldest->id, $middle->id, $newest->id])
        ->and($descending)->toBe([$newest->id, $middle->id, $oldest->id]);
});

test('iris blogs without a published date in the layout fall back to the webpage dates', function () {
    $withLayoutDate = createBlogWebpage($this->website, 'Blog with layout date', [
        'live_at'          => '2026-01-01 10:00:00',
        'published_layout' => publishedLayoutWithDate('2026-05-05T10:00:00.000Z'),
    ]);

    $withoutLayoutDate = createBlogWebpage($this->website, 'Blog without layout date', [
        'live_at'           => '2026-01-02 10:00:00',
        'last_published_at' => '2026-06-06 10:00:00',
        'published_layout'  => publishedLayoutWithDate(null),
    ]);

    request()->query->set('sort', 'last_published_at');

    expect(IndexIrisBlogs::make()->handle($this->website)->pluck('id')->all())
        ->toBe([$withLayoutDate->id, $withoutLayoutDate->id]);
});

test('renaming a blog url refreshes its canonical url and only redirects the old url once the blog is live', function () {
    $draft = createBlogWebpage($this->website, 'Draft blog', ['state' => WebpageStateEnum::IN_PROCESS, 'url' => 'draft-blog-renamed']);

    ProcessUpdateWebpageUrl::run($draft, 'draft-blog-old');

    expect($draft->refresh()->canonical_url)->toEndWith('/draft-blog-renamed')
        ->and(Redirect::where('website_id', $this->website->id)->where('from_path', 'draft-blog-old')->exists())->toBeFalse();

    $live = createBlogWebpage($this->website, 'Live blog', ['url' => 'live-blog-renamed']);

    ProcessUpdateWebpageUrl::run($live, 'live-blog-old');

    expect($live->refresh()->canonical_url)->toEndWith('/live-blog-renamed')
        ->and(Redirect::where('website_id', $this->website->id)->where('from_path', 'live-blog-old')->value('to_webpage_id'))->toBe($live->id);
});
