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


    case SHOWCASE            = 'showcase';
    case STOCKS              = 'stocks';

    case PALLETS              = 'pallets';
    case STOCK_MOVEMENTS      = 'stock_movements';

    case HISTORY            = 'history';
    case DATA               = 'data';



    public function blueprint(): array
    {
        return match ($this) {
            LocationTabsEnum::STOCKS => [
                'title' => 'SKUs',
                'icon'  => 'fal fa-box',
            ],
            LocationTabsEnum::PALLETS => [
                'title' => __('pallets'),
                'icon'  => 'fal fa-pallet',
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
            LocationTabsEnum::SHOWCASE => [
                'title' => __('location'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
