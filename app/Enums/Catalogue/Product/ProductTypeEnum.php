<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 15:47:35 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Catalogue\Product;

use App\Enums\EnumHelperTrait;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;

enum ProductTypeEnum: string
{
    use EnumHelperTrait;

    case PHYSICAL_GOOD         = 'physical_good';
    case SERVICE               = 'service';
    case SUBSCRIPTION          = 'subscription';
    case RENTAL                = 'rental';

    public static function labels(Shop|Organisation $parent=null): array
    {

        $labels= [
            'physical_good'      => __('Physical goods'),
            'service'            => __('Services'),
            'subscription'       => __('Subscriptions'),
            'rental'             => __('Rentals'),
        ];

        if($parent instanceof Shop) {
            unset($labels['subscription']);
            if($parent->type!=ShopTypeEnum::FULFILMENT) {
                unset($labels['rental']);
            }
        }
        return $labels;

        // return $this->filter($parent,$labels);

    }

    /*
    private function filter($parent, $cases): Array
    {
        if($parent instanceof Shop) {
            unset($cases['subscription']);
            if($parent->type!=ShopTypeEnum::FULFILMENT) {
                unset($cases['rental']);
            }
        }
        return $cases;
    }
    */

    public static function typeIcon(): array
    {
        return [
            'physical_good' => [
                'tooltip' => __('Physical good'),
                'icon'    => 'fal fa-cube',
                'app'     => [
                    'name' => 'cube',
                    'type' => 'font-awesome-5'
                ]
            ],
            'subscription' => [
                'tooltip' => __('Subscription'),
                'icon'    => 'fal fa-bell',
                'app'     => [
                    'name' => 'bell',
                    'type' => 'font-awesome-5'
                ]
            ],
            'service' => [
                'tooltip' => __('Service'),
                'icon'    => 'fal fa-concierge-bell',
                'app'     => [
                    'name' => 'concierge-bell',
                    'type' => 'font-awesome-5'
                ]
            ],
            'rental' => [
                'tooltip' => __('Rental'),
                'icon'    => 'fal fa-garage',
                'app'     => [
                    'name' => 'garage',
                    'type' => 'font-awesome-5'
                ]
            ],
        ];
    }

    public static function count(Shop|Organisation $parent): array
    {
        $stats =$parent->stats;
        $counts= [
            'physical_good'         => $stats->number_products_type_physical_good,
            'subscription'          => $stats->number_products_type_subscription,
            'service'               => $stats->number_products_type_service,
            'rental'                => $stats->number_products_type_rental,
        ];

        if($parent instanceof Shop) {
            unset($counts['subscription']);
            if($parent->type!=ShopTypeEnum::FULFILMENT) {
                unset($counts['rental']);
            }
        }
        return $counts;

    }

}
