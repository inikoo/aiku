<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 May 2024 19:47:57 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier\UI;

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
                // 'address'  => AddressResource::make($supplier->getAddress('supplier'))->getArray(), Todo: Need Fix
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
