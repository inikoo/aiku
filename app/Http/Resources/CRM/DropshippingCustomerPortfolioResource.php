<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Apr 2024 14:30:26 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\CRM;

use App\Http\Resources\HasSelfCall;
use App\Models\CRM\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $number_current_clients
 */
class DropshippingCustomerPortfolioResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var Customer $customer */
        $portfolio = $this;

        return [
            'reference'              => $portfolio->reference,
            'product_name'           => $portfolio->product_name,
            'product_code'           => $portfolio->product_code,
            'slug'                   => $portfolio->slug,
            'created_at'             => $portfolio->created_at,
            'routes'                 => [
                'delete_route' => [
                    'method'     => 'delete',
                    'name'       => 'grp.models.org.shop.customer.portfolio.delete',
                    'parameters' => [
                        'organisation' => $portfolio->organisation_id,
                        'shop'         => $portfolio->shop_id,
                        'customer'     => $portfolio->customer_id,
                        'portfolio'    => $portfolio->id
                    ]
                ]
            ]

        ];
    }
}
