<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\WebsiteVisitor;

use App\Models\CRM\WebUser;
use App\Models\Web\WebsiteVisitor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class LinkAnonymousVisitorsToWebUser
{
    use AsAction;

    public const int LOOKBACK_DAYS = 90;

    public function handle(WebUser $webUser, int $websiteId, string $visitorHash, ?int $anchorVisitorId = null, ?Carbon $until = null, ?string $visitorUuid = null): int
    {
        return $this->anonymousVisitors($webUser, $websiteId, $visitorHash, $anchorVisitorId, $until ?? now(), $visitorUuid)
            ->update(['web_user_id' => $webUser->id]);
    }

    /**
     * @return Builder<WebsiteVisitor>
     */
    public function anonymousVisitors(WebUser $webUser, int $websiteId, string $visitorHash, ?int $anchorVisitorId, Carbon $until, ?string $visitorUuid = null): Builder
    {
        $windowStart = $until->copy()->subDays(self::LOOKBACK_DAYS);

        $sharedWithOtherWebUser = WebsiteVisitor::query()
            ->where('website_id', $websiteId)
            ->where('visitor_hash', $visitorHash)
            ->where('first_seen_at', '>=', $windowStart)
            ->whereNotNull('web_user_id')
            ->where('web_user_id', '!=', $webUser->id)
            ->exists();

        return WebsiteVisitor::query()
            ->where('website_id', $websiteId)
            ->whereNull('web_user_id')
            ->where(function ($query) use ($anchorVisitorId, $visitorHash, $visitorUuid, $sharedWithOtherWebUser, $windowStart, $until) {
                $query->when($anchorVisitorId, fn ($anchor) => $anchor->where('id', $anchorVisitorId), fn ($anchor) => $anchor->whereRaw('false'));

                if ($visitorUuid && Str::isUuid($visitorUuid)) {
                    $query->orWhere(function ($sameBrowser) use ($visitorUuid, $windowStart, $until) {
                        $sameBrowser->where('visitor_uuid', $visitorUuid)
                            ->whereBetween('first_seen_at', [$windowStart, $until]);
                    });
                }

                if (! $sharedWithOtherWebUser) {
                    $query->orWhere(function ($sameVisitor) use ($visitorHash, $windowStart, $until) {
                        $sameVisitor->where('visitor_hash', $visitorHash)
                            ->whereBetween('first_seen_at', [$windowStart, $until]);
                    });
                }
            });
    }
}
