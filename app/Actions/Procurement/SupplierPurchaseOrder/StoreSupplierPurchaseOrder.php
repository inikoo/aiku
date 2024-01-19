<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierPurchaseOrder;

use App\Actions\Procurement\SupplierPurchaseOrder\Traits\HasHydrators;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreSupplierPurchaseOrder
{
    use AsAction;
    use WithAttributes;
    use HasHydrators;


    private Supplier|Agent $parent;

    public function handle(Agent|Supplier $parent, array $modelData): PurchaseOrder
    {
        /** @var PurchaseOrder $supplierPurchaseOrder */
        $supplierPurchaseOrder = $parent->purchaseOrders()->create($modelData);

        $this->getHydrators($supplierPurchaseOrder);

        return $supplierPurchaseOrder;
    }

    public function rules(): array
    {
        return [
            'number'        => ['required', 'numeric', 'unique:purchase_orders,number'],
            'date'          => ['required', 'date']
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $supplierPurchaseOrder = $this->parent->purchaseOrders()->count();

        if($supplierPurchaseOrder>= 1) {
            $validator->errors()->add('purchase_order', 'Are you sure want to create new supplier purchase order?');
        }
    }

    public function action(Agent|Supplier $parent, array $modelData): PurchaseOrder
    {
        $this->parent = $parent;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }
}
