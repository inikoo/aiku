<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ProspectTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case ITEMS                 = 'items';
    case PAYMENTS              = 'payments';
    case PROPERTIES_OPERATIONS = 'properties_operations';

    case CHANGELOG             = 'changelog';



    public function blueprint(): array
    {
        return match ($this) {
            ProspectTabsEnum::ITEMS => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            ProspectTabsEnum::PAYMENTS => [
                'title' => __('subcategories'),
            ],
            ProspectTabsEnum::PROPERTIES_OPERATIONS => [
                'title' => __('sales'),
                'icon'  => 'fal fa-money-bill-wave',
            ],ProspectTabsEnum::CHANGELOG => [
                'title' => __('customers'),
                'icon'  => 'fal fa-user',
            ],
        };
    }
}
