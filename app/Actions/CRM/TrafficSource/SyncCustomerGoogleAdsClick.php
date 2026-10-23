<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Fri, 23 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\TrafficSource;

use App\Models\CRM\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncCustomerGoogleAdsClick
{
    use AsAction;

    /**
     * Keeps the most recent Google ad click of any of the customer's browsers, so an order submitted
     * without a browser (payment webhook, sweeper) still knows which click led to it.
     */
    public function handle(Customer $customer, ?string $cookie): bool
    {
        $click = ParseGoogleAdsClick::run($cookie);

        if (!$click || !ParseGoogleAdsClick::isWithinWindow($click['clicked_at'])) {
            return false;
        }

        if ($customer->google_ads_clicked_at && $customer->google_ads_clicked_at->gte($click['clicked_at'])) {
            return false;
        }

        $customer->updateQuietly([
            'google_ads_click_id'             => $click['click_id'],
            'google_ads_click_id_type'        => $click['click_id_type'],
            'google_ads_clicked_at'           => $click['clicked_at'],
            'google_ads_ad_user_data_consent' => null,
        ]);

        return true;
    }
}
