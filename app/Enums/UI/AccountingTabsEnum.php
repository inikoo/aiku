<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum AccountingTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case ITEMS                 = 'items';
    case PAYMENTS              = 'payments';
    case PROPERTIES_OPERATIONS = 'properties_operations';

    case CHANGELOG = 'changelog';


    public function blueprint(): array
    {
        return match ($this) {
            AccountingTabsEnum::ITEMS => [
                'title' => __('items'),
                'icon'  => 'fal fa-bars',
            ],
            AccountingTabsEnum::PAYMENTS => [
                'title' => __('payments'),
                'icon'  => 'fal fa-dollar-sign',
            ],
            AccountingTabsEnum::PROPERTIES_OPERATIONS => [
                'title' => __('properties/operations'),
                'icon'  => 'fal fa-database',
            ],
            AccountingTabsEnum::CHANGELOG => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-road',
                'type'  => 'icon-only',
            ],
        };
    }
}
