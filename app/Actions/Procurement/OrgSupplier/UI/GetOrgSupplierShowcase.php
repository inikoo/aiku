<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 01:01:23 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplier\UI;

use App\Http\Resources\Helpers\AddressResource;
use App\Models\Procurement\OrgSupplier;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOrgSupplierShowcase
{
    use AsObject;

    public function handle(OrgSupplier $orgSupplier): array
    {

        $supplier = $orgSupplier->supplier;

        return [
            'contactCard' => [
                'company'  => $supplier->company_name,
                'contact'  => $supplier->contact_name,
                'email'    => $supplier->email,
                'phone'    => $supplier->phone,
                'address'  => AddressResource::make($supplier->address)->getArray(),
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
                    'value' => $supplier->stats->number_stock_deliveries
                ],

            ]
        ];
    }
}
