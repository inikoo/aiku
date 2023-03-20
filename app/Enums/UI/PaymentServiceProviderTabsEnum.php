<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Mar 2023 01:54:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum PaymentServiceProviderTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case STATS            = 'stats';
    case PAYMENT_ACCOUNTS = 'payment_accounts';
    case PAYMENTS         = 'payments';
    case DATA             = 'data';
    case CHANGELOG        = 'changelog';

    public function blueprint(): array
    {
        return match ($this) {
            PaymentServiceProviderTabsEnum::STATS             => [
                'title' => __('stats'),
                'icon'  => 'fal fa-chart-line',
            ],
            PaymentServiceProviderTabsEnum::PAYMENT_ACCOUNTS => [
                'title' => __('payment accounts'),
                'icon'  => 'fal fa-money-check-alt',
            ],
            PaymentServiceProviderTabsEnum::PAYMENTS     => [
                'title' => __('payments'),
                'icon'  => 'fal fa-credit-card',
            ],
            PaymentServiceProviderTabsEnum::DATA     => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            PaymentServiceProviderTabsEnum::CHANGELOG     => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-clock',
            ],
        };
    }
}
