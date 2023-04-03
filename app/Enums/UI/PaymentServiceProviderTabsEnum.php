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

    case SHOWCASE         = 'showcase';
    case STATS            = 'stats';
    case PAYMENT_ACCOUNTS = 'payment_accounts';
    case PAYMENTS         = 'payments';
    case HISTORY          = 'history';
    case DATA             = 'data';


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
                'type'  => 'icon',
                'align' => 'right',
            ],
            PaymentServiceProviderTabsEnum::HISTORY     => [
                'title'  => __('history'),
                'icon'   => 'fal fa-clock',
                'type'   => 'icon',
                'align'  => 'right',
            ],
            PaymentServiceProviderTabsEnum::SHOWCASE => [
                'title' => __('payment service provider'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
