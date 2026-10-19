<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\WebsitePageView;

use App\Actions\Web\Webpage\ResolveWebpageByPath;
use App\Models\Web\Website;
use App\Models\Web\WebsitePageView;
use App\Models\Web\WebsiteVisitor;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebsitePageView implements ShouldBeUnique
{
    use AsAction;

    public string $jobQueue = 'analytics';
    public int $jobTimeout = 120;
    public int $jobTries = 1;

    public function getJobUniqueId(WebsiteVisitor $visitor, Website $website, string $url): string
    {
        return "$visitor->id:$website->id:$url";
    }

    public function handle(
        WebsiteVisitor $visitor,
        Website $website,
        string $url
    ): WebsitePageView {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        $lastPageView = WebsitePageView::on('aiku_no_sticky')
            ->where('website_visitor_id', $visitor->id)
            ->latest('id')
            ->first();

        if ($lastPageView) {
            $duration = min((int) abs(now()->diffInSeconds($lastPageView->created_at)), 1800);
            $lastPageView->update(['duration_seconds' => $duration]);
        }

        $webpage = ResolveWebpageByPath::run($website, $path);

        return WebsitePageView::on('aiku_no_sticky')->create([
            'group_id'           => $visitor->group_id,
            'organisation_id'    => $visitor->organisation_id,
            'website_visitor_id' => $visitor->id,
            'webpage_id'         => $webpage?->id,
            'website_id'         => $website->id,
            'shop_id'            => $website->shop_id,
            'page_url'           => $url,
            'page_path'          => $path,
            'page_type'          => $webpage?->type,
            'page_sub_type'      => $webpage?->sub_type,
            'view_date'          => now()->toDateString(),
        ]);
    }
}
