<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum LocationTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case DATA               = 'data';
    case PARTS              = 'parts';
    case STOCK_MOVEMENTS    = 'stock_movements';

    case CHANGELOG          = 'changelog';


    public function blueprint(): array
    {
        return match ($this) {
            LocationTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            LocationTabsEnum::PARTS => [
                'title' => __('parts'),
                'icon'  => 'fal fa-box',
            ],
            LocationTabsEnum::STOCK_MOVEMENTS => [
                'title' => __('stock movements'),
                'icon'  => 'fal fa-exchange',
            ],
            LocationTabsEnum::CHANGELOG => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-road',
                'type'  => 'icon-only',
            ],
        };
    }
}
