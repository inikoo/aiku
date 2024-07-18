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
use App\Models\SysAdmin\Organisation;

enum ProductCategoryStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS    = 'in-process';
    case ACTIVE        = 'active';
    case DISCONTINUING = 'discontinuing';
    case DISCONTINUED  = 'discontinued';

    public static function labels($forElements = false): array
    {
        return [
            'in-process'            => __('In Process'),
            'active'                => __('Active'),
            'discontinuing'         => __('Discontinuing'),
            'discontinued'          => __('Discontinued'),
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'in-process' => [
                'tooltip' => __('In process'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-lime-500',
                'color'   => 'lime',
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'active' => [
                'tooltip' => __('Active'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-emerald-500',
                'color'   => 'emerald',
                'app'     => [
                    'name' => 'spell-check',
                    'type' => 'font-awesome-5'
                ]
            ],
           'discontinuing' => [
                'tooltip' => __('Discontinuing'),
                'icon'    => 'fal fa-exclamation-triangle',
                'class'   => 'text-orange-500',
                'color'   => 'orange',
                'app'     => [
                    'name' => 'exclamation-triangle',
                    'type' => 'font-awesome-5'
                ]
            ],
            'discontinued'  => [
                'tooltip' => __('Discontinued'),
                'icon'    => 'fal fa-times',
                'class'   => 'text-red-500',
                'color'   => 'red',
                'app'     => [
                    'name' => 'times',
                ]
            ],
        ];
    }

    public static function countDepartment(Shop|Organisation $parent): array
    {
        if($parent instanceof Organisation) {
            $stats = $parent->catalogueStats;
        } else {
            $stats = $parent->stats;
        }

        return [
            'in-process'            => $stats->number_departments_state_in_process,
            'active'                => $stats->number_departments_state_active,
            'discontinuing'         => $stats->number_departments_state_discontinuing,
            'discontinued'          => $stats->number_departments_state_discontinued,
        ];
    }

    public static function countFamily(Shop|ProductCategory|Organisation $parent): array
    {
        if($parent instanceof Organisation) {
            $stats = $parent->catalogueStats;
        } else {
            $stats = $parent->stats;
        }

        return [
            'in-process'            => $stats->number_families_state_in_process,
            'active'                => $stats->number_families_state_active,
            'discontinuing'         => $stats->number_families_state_discontinuing,
            'discontinued'          => $stats->number_families_state_discontinued,
        ];
    }

    public static function countSubDepartment(ProductCategory $parent): array
    {
        $stats = $parent->stats;

        return [
            'in-process'            => $stats->number_sub_departments_state_in_process,
            'active'                => $stats->number_sub_departments_state_active,
            'discontinuing'         => $stats->number_sub_departments_state_discontinuing,
            'discontinued'          => $stats->number_sub_departments_state_discontinued,
        ];
    }
}
