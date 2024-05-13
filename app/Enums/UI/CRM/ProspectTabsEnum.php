<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:13:49 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\CRM;

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
