<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 04 May 2024 12:55:09 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Agent\UI;

use App\Http\Resources\Helpers\AddressResource;
use App\Models\SupplyChain\Agent;
use Lorisleiva\Actions\Concerns\AsObject;

class GetAgentShowcase
{
    use AsObject;

    public function handle(Agent $agent): array
    {
        return [
            'contactCard' => [
                'company' => $agent->organisation->company_name,
                'contact' => $agent->organisation->contact_name,
                'email'   => $agent->organisation->email,
                'phone'   => $agent->organisation->phone,
                'address' => AddressResource::make($agent->organisation->address),
                'photo'   => $agent->organisation->getPhoto()
            ],
            'stats'       => [
                [
                    'label' => __('suppliers'),
                    'value' => $agent->stats->number_suppliers
                ],
                [
                    'label' => __('products'),
                    'value' => $agent->stats->number_supplier_products
                ],
                [
                    'label' => __('purchase orders'),
                    'value' => $agent->stats->number_purchase_orders
                ],
                [
                    'label' => __('deliveries'),
                    'value' => $agent->stats->number_stock_deliveries
                ],

            ]
        ];
    }
}
