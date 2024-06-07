<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 01:37:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Catalogue\ProductCategory;

use App\Enums\EnumHelperTrait;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;

enum ProductCategoryStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS    = 'in-process';
    case ACTIVE        = 'active';
    case DISCONTINUING = 'discontinuing';
    case DISCONTINUED  = 'discontinued';

    public static function labels(): array
    {
        return [
            'in-process' => __('In Process'),
            'active'    => __('Active'),
            'discontinuing'         => __('Discontinuing'),
            'discontinued'      => __('Discontinued'),
        ];
    }

    public static function countDepartment(Shop $parent): array
    {
        $stats = $parent->stats;
        return [
            'in-process'            => $stats->number_departments_state_in_process,
            'active'                => $stats->number_departments_state_active,
            'discontinuing'         => $stats->number_departments_state_discontinuing,
            'discontinued'          => $stats->number_departments_state_discontinued,
        ];
    }

    public static function countFamily(Shop|ProductCategory $parent): array
    {
        $stats = $parent->stats;

        return [
            'in-process' => $stats->number_families_state_in_process,
            'active'    => $stats->number_families_state_active,
            'discontinuing'         => $stats->number_families_state_discontinuing,
            'discontinued'      => $stats->number_families_state_discontinued,
        ];
    }
}
