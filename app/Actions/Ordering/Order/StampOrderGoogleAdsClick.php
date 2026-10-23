<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Fri, 23 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Ordering\Order;

use App\Actions\CRM\TrafficSource\ParseGoogleAdsClick;
use App\Actions\CRM\TrafficSource\SyncCustomerGoogleAdsClick;
use App\Models\Ordering\Order;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class StampOrderGoogleAdsClick
{
    use AsAction;

    /**
     * Freezes on the order the Google ad click that led to it, read from the customer because the
     * click log is pruned after the conversion window and an order can be submitted with no browser.
     * The submitting browser is read first in case its click has not reached the customer yet.
     */
    public function handle(Order $order): void
    {
        try {
            $this->stamp($order);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function stamp(Order $order): void
    {
        if ($order->google_ads_click_id || !$order->customer) {
            return;
        }

        $customer = $order->customer;
        $cookie   = request()->cookie(ParseGoogleAdsClick::COOKIE);

        if (filled($cookie)) {
            SyncCustomerGoogleAdsClick::run($customer, $cookie);
        }

        if (!$customer->google_ads_click_id || !ParseGoogleAdsClick::isWithinWindow($customer->google_ads_clicked_at, $order->submitted_at ?? now())) {
            return;
        }

        $order->updateQuietly([
            'google_ads_click_id'             => $customer->google_ads_click_id,
            'google_ads_click_id_type'        => $customer->google_ads_click_id_type,
            'google_ads_clicked_at'           => $customer->google_ads_clicked_at,
            'google_ads_ad_user_data_consent' => $customer->google_ads_ad_user_data_consent,
        ]);
    }
}
