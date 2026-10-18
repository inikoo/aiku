<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Mon, 12 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\Customer;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsObject;

class GetCustomerStateByLastInvoice
{
    use AsObject;

    public const int DEFAULT_LOSING_AFTER_DAYS = 180;
    public const int DEFAULT_LOST_AFTER_DAYS   = 365;

    public const array RECALCULATED_STATES = [
        CustomerStateEnum::REGISTERED->value,
        CustomerStateEnum::ACTIVE->value,
        CustomerStateEnum::LOSING->value,
        CustomerStateEnum::LOST->value,
    ];

    public function handle(Customer $customer): ?CustomerStateEnum
    {
        if (!$this->appliesToShop($customer->shop)
            || $customer->status !== CustomerStatusEnum::APPROVED
            || !in_array($customer->state->value, self::RECALCULATED_STATES)) {
            return null;
        }

        return $this->stateFor($customer->shop, $customer->last_invoiced_at);
    }

    public function appliesToShop(Shop $shop): bool
    {
        return $shop->is_aiku && $shop->type !== ShopTypeEnum::FULFILMENT;
    }

    public function stateFor(Shop $shop, ?Carbon $lastInvoicedAt): CustomerStateEnum
    {
        if (!$lastInvoicedAt) {
            return CustomerStateEnum::REGISTERED;
        }

        return match (true) {
            $lastInvoicedAt->gte($this->activeSince($shop)) => CustomerStateEnum::ACTIVE,
            $lastInvoicedAt->gte($this->losingSince($shop)) => CustomerStateEnum::LOSING,
            default                                         => CustomerStateEnum::LOST,
        };
    }

    public function activeSince(Shop $shop): Carbon
    {
        return now()->subDays($this->losingAfterDays($shop))->startOfDay();
    }

    public function losingSince(Shop $shop): Carbon
    {
        return now()->subDays($this->lostAfterDays($shop))->startOfDay();
    }

    public function losingAfterDays(Shop $shop): int
    {
        return (int) Arr::get($shop->settings, 'customer.losing_after_days', self::DEFAULT_LOSING_AFTER_DAYS);
    }

    public function lostAfterDays(Shop $shop): int
    {
        return (int) Arr::get($shop->settings, 'customer.lost_after_days', self::DEFAULT_LOST_AFTER_DAYS);
    }
}
