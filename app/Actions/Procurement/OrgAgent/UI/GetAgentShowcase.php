<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent\UI;

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
                'company'  => $agent->company_name,
                'contact'  => $agent->contact_name,
                'email'    => $agent->email,
                'phone'    => $agent->phone,
                'address'  => AddressResource::make($agent->getAddress())->getArray(),
                'image_id' => $agent->image_id
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
                    'value' => $agent->stats->number_supplier_deliveries
                ],

            ]
        ];
    }
}
