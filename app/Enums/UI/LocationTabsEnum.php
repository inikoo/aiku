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


    case PARTS              = 'parts';
    case STOCK_MOVEMENTS    = 'stock_movements';

    case HISTORY            = 'history';
    case DATA               = 'data';



    public function blueprint(): array
    {
        return match ($this) {
            LocationTabsEnum::PARTS => [
                'title' => __('parts'),
                'icon'  => 'fal fa-box',
            ],
            LocationTabsEnum::STOCK_MOVEMENTS => [
                'title' => __('stock movements'),
                'icon'  => 'fal fa-exchange',
            ],
            LocationTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            LocationTabsEnum::DATA => [
                'type'  => 'icon',
                'align' => 'right',
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
        };
    }
}
