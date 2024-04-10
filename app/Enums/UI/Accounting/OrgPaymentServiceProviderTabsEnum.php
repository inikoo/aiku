<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Apr 2024 20:09:25 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Accounting;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OrgPaymentServiceProviderTabsEnum: string
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
            OrgPaymentServiceProviderTabsEnum::STATS             => [
                'title' => __('stats'),
                'icon'  => 'fal fa-chart-line',
            ],
            OrgPaymentServiceProviderTabsEnum::PAYMENT_ACCOUNTS => [
                'title' => __('payment accounts'),
                'icon'  => 'fal fa-money-check-alt',
            ],
            OrgPaymentServiceProviderTabsEnum::PAYMENTS     => [
                'title' => __('payments'),
                'icon'  => 'fal fa-coins',
            ],
            OrgPaymentServiceProviderTabsEnum::DATA     => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            OrgPaymentServiceProviderTabsEnum::HISTORY     => [
                'title'  => __('history'),
                'icon'   => 'fal fa-clock',
                'type'   => 'icon',
                'align'  => 'right',
            ],
            OrgPaymentServiceProviderTabsEnum::SHOWCASE => [
                'title' => __('payment service provider'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
