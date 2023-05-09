<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum PurchaseOrderTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE            = 'SHOWCASE';

    case ITEMS               = 'items';

    case HISTORY             = 'history';

    case DATA                = 'data';





    public function blueprint(): array
    {
        return match ($this) {
            PurchaseOrderTabsEnum::DATA     => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            PurchaseOrderTabsEnum::ITEMS  => [
                'title' => __('items'),
                'icon'  => 'fal fa-bars',
            ],
            PurchaseOrderTabsEnum::SHOWCASE => [
                'title' => __('purchase orders'),
                'icon'  => 'fal fa-info-circle',
            ],
            PurchaseOrderTabsEnum::HISTORY     => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
