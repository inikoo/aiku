<?php

use App\Http\Middleware\DetectIrisWebsite;
use App\Http\Middleware\HandleIrisInertiaRequests;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

test('iris pagination links keep only the query the visitor sent, not the website data merged into the request', function () {
    $request = Request::create('/blog?blogsPage=2', 'GET');
    app()->instance('request', $request);
    (new DetectIrisWebsite())->applyWebsiteData($request, ['domain' => 'example.com', 'currency_data' => ['code' => 'GBP'], 'locale' => 'en']);

    $paginator = (new LengthAwarePaginator(range(1, 30), 30, 10, 2, ['path' => '/blog', 'pageName' => 'blogsPage']))->withQueryString();

    expect($paginator->url(3))->toBe('/blog?blogsPage=3');
});

test('iris canonical url keeps the pagination page and drops every other query parameter', function () {
    $canonicalUrl = fn (string $uri) => (new HandleIrisInertiaRequests())->canonicalUrl(Request::create($uri, 'GET'));

    expect($canonicalUrl('https://example.com/blog?blogsPage=3&currency_data%5Bcode%5D=GBP&website%5Bexists%5D=1&locale=en'))->toBe('https://example.com/blog?blogsPage=3')
        ->and($canonicalUrl('https://example.com/blog?blogsPage=1&sort=-date'))->toBe('https://example.com/blog')
        ->and($canonicalUrl('https://example.com/reviews?page=2'))->toBe('https://example.com/reviews?page=2');
});
