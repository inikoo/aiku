<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jul 2024 00:55:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Fulfilment;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum FulfilmentCustomerPalletsTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case STORING    = 'storing';
    case INCOMING   = 'incoming';

    case ALL        = 'all';
    case RETURNED   = 'returned';
    case INCIDENT   = 'incident';

    public function blueprint(): array
    {
        return match ($this) {
            FulfilmentCustomerPalletsTabsEnum::STORING => [
                'title' => __('Storing'),
                'icon'  => 'fal fa-warehouse-alt',
            ],
            FulfilmentCustomerPalletsTabsEnum::INCOMING => [
                'title' => __('In process'),
                'icon'  => 'fal fa-seedling',
            ],
            FulfilmentCustomerPalletsTabsEnum::INCIDENT => [
                'title' => __('Incidents'),
                'icon'  => 'fal fa-sad-cry',
            ],

            FulfilmentCustomerPalletsTabsEnum::RETURNED => [
                'title' => __('returned'),
                'icon'  => 'fal fa-arrow-alt-from-left',
            ],

            FulfilmentCustomerPalletsTabsEnum::ALL => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('All'),
                'icon'  => 'fal fa-align-justify',
            ],

        };
    }
}
