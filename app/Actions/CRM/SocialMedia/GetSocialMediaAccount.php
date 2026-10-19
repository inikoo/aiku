<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\SocialMedia;

use App\Enums\CRM\SocialMedia\SocialMediaPlatformEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\SocialMediaAccount;
use Exception;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Accounts are found by the platform's own id when the source carries one. A source without it
 * (an account export, a TikTok file) goes to the shop's one account on that platform, and an
 * account first created that way takes the id the first time a source names it.
 */
class GetSocialMediaAccount
{
    use AsAction;

    /**
     * @throws Exception
     */
    public function handle(Shop $shop, SocialMediaPlatformEnum $platform, ?string $externalId, ?string $name = null, ?string $username = null): SocialMediaAccount
    {
        $accounts      = $shop->socialMediaAccounts()->where('platform', $platform->value)->get();
        $platformLabel = SocialMediaPlatformEnum::labels()[$platform->value];

        if ($externalId === null && $accounts->count() > 1) {
            throw new Exception(__('This shop has more than one :platform account and the file does not say which one', ['platform' => $platformLabel]));
        }

        $account = $externalId === null
            ? $accounts->first()
            : ($accounts->firstWhere('external_id', $externalId) ?? $accounts->whereNull('external_id')->first());

        if ($account === null) {
            return $shop->socialMediaAccounts()->create([
                'group_id'        => $shop->group_id,
                'organisation_id' => $shop->organisation_id,
                'platform'        => $platform,
                'external_id'     => $externalId,
                'name'            => $name ?? $username ?? $shop->name.' '.$platformLabel,
                'username'        => $username,
            ]);
        }

        if ($externalId !== null && ($account->external_id === null || ($name !== null && $account->name !== $name))) {
            $account->update(array_filter([
                'external_id' => $externalId,
                'name'        => $name,
                'username'    => $username,
            ]));
        }

        return $account;
    }
}
