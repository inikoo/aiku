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
use Lorisleiva\Actions\Concerns\AsAction;

class LinkAnonymousVisitorsToWebUser
{
    use AsAction;

    public const int LOOKBACK_DAYS = 90;

    public function handle(WebUser $webUser, WebsiteVisitor $registrationVisitor, ?Carbon $registeredAt = null): int
    {
        return $this->anonymousVisitors($webUser, $registrationVisitor, $registeredAt ?? now())->update(['web_user_id' => $webUser->id]);
    }

    /**
     * @return Builder<WebsiteVisitor>
     */
    public function anonymousVisitors(WebUser $webUser, WebsiteVisitor $registrationVisitor, Carbon $registeredAt): Builder
    {
        $windowStart = $registeredAt->copy()->subDays(self::LOOKBACK_DAYS);

        $sharedWithOtherWebUser = WebsiteVisitor::query()
            ->where('website_id', $registrationVisitor->website_id)
            ->where('visitor_hash', $registrationVisitor->visitor_hash)
            ->where('first_seen_at', '>=', $windowStart)
            ->whereNotNull('web_user_id')
            ->where('web_user_id', '!=', $webUser->id)
            ->exists();

        return WebsiteVisitor::query()
            ->where('website_id', $registrationVisitor->website_id)
            ->whereNull('web_user_id')
            ->where(function ($query) use ($registrationVisitor, $sharedWithOtherWebUser, $windowStart, $registeredAt) {
                $query->where('id', $registrationVisitor->id);

                if (! $sharedWithOtherWebUser) {
                    $query->orWhere(function ($sameVisitor) use ($registrationVisitor, $windowStart, $registeredAt) {
                        $sameVisitor->where('visitor_hash', $registrationVisitor->visitor_hash)
                            ->whereBetween('first_seen_at', [$windowStart, $registeredAt]);
                    });
                }
            });
    }
}
