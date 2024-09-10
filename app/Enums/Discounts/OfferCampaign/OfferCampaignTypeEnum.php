<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 14:26:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Discounts\OfferCampaign;

use App\Enums\EnumHelperTrait;

enum OfferCampaignTypeEnum: string
{
    use EnumHelperTrait;

    case ORDER_RECURSION   = 'order-recursion';
    case VOLUME_DISCOUNT   = 'volume-discount';
    case FIRST_ORDER       = 'first-order';
    case CUSTOMER_OFFERS   = 'customer-offers';
    case SHOP_OFFERS       = 'shop-offers';
    case COLLECTION_OFFERS = 'collection-offers';
    case PRODUCT_OFFERS    = 'product-offers';


    public function labels(): array
    {
        return [
            'order-recursion'   => __('Order recursion'),
            'volume-discount'   => __('Volume discount'),
            'first-order'       => __('First order'),
            'customer-offers'   => __('Customer offers'),
            'shop-offers'       => __('Shop offers'),
            'collection-offers' => __('Collection offers'),
            'product-offers'    => __('Product offers')
        ];
    }

    public function codes(): array
    {
        return [
            'order-recursion'   => 'OR',
            'volume-discount'   => 'VL',
            'first-order'       => 'FO',
            'customer-offers'   => 'CU',
            'shop-offers'       => 'SO',
            'collection-offers' => 'CO',
            'product-offers'    => 'PO'
        ];
    }

}
