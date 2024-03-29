<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier\UI;

use App\Http\Resources\Helpers\AddressResource;
use App\Models\SupplyChain\Supplier;
use Lorisleiva\Actions\Concerns\AsObject;

class GetSupplierShowcase
{
    use AsObject;

    public function handle(Supplier $supplier): array
    {
        return [
            'contactCard' => [
                'company'  => $supplier->company_name,
                'contact'  => $supplier->contact_name,
                'email'    => $supplier->email,
                'phone'    => $supplier->phone,
                'address'  => AddressResource::make($supplier->getAddress())->getArray(),
                'image_id' => $supplier->image_id
            ],
            'stats'       => [
                [
                    'label' => __('products'),
                    'value' => $supplier->stats->number_supplier_products
                ],
                [
                    'label' => __('purchase orders'),
                    'value' => $supplier->stats->number_purchase_orders
                ],
                [
                    'label' => __('deliveries'),
                    'value' => $supplier->stats->number_supplier_deliveries
                ],

            ]
        ];
    }
}
