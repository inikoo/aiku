<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Mon, 19 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Webpage;

use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class ResolveWebpageByPath
{
    use AsAction;

    /**
     * The live webpage a visit's path belongs to, for the analytics jobs. A path with no webpage is
     * cached as `false`, for an hour, because the cache cannot tell a stored null from a missing
     * key. Every reader of this key goes through here so none of them has to know that.
     */
    public function handle(Website $website, string $path): ?Webpage
    {
        $cacheKey = "webpage_resolve:$website->id:".md5($path);

        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached ?: null;
        }

        $webpage = Webpage::on('aiku_no_sticky')
            ->where('website_id', $website->id)
            ->where('state', WebpageStateEnum::LIVE)
            ->where(function ($query) use ($path) {
                $query->whereRaw("substring(canonical_url from 'https?://[^/]+(/.*)$') = ?", [$path])
                    ->orWhere('url', ltrim($path, '/'));
            })
            ->first();

        Cache::put($cacheKey, $webpage ?? false, $webpage ? 86400 : 3600);

        return $webpage;
    }
}
