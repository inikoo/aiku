<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDelivery;

use App\Actions\Procurement\Agent\Hydrators\AgentHydrateSupplierDeliveries;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateSupplierDeliveries;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Models\Procurement\Agent;
use App\Models\Procurement\SupplierDelivery;
use App\Models\Procurement\Supplier;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreSupplierDelivery
{
    use AsAction;
    use WithAttributes;

    private bool $force;

    private Supplier|Agent $parent;

    public function handle(Agent|Supplier $parent, array $modelData): SupplierDelivery
    {
        /** @var SupplierDelivery $supplierDelivery */
        $supplierDelivery = $parent->supplierDeliveries()->create($modelData);

        if(class_basename($parent) == 'Supplier') {
            SupplierHydrateSupplierDeliveries::dispatch($parent);
        } else {
            AgentHydrateSupplierDeliveries::dispatch($parent);
        }

        TenantHydrateProcurement::dispatch(app('currentTenant'));

        return $supplierDelivery;
    }

    public function rules(): array
    {
        return [
            'number'        => ['required', 'numeric', 'unique:group.supplier_deliveries,number'],
            'date'          => ['required', 'date'],
            'currency_id'   => ['required', 'exists:currencies,id'],
            'exchange'      => ['required', 'numeric']
        ];
    }

     public function afterValidator(Validator $validator): void
     {
         $supplierDelivery = $this->parent->SupplierDeliveries()->count();

         if(!$this->force && $supplierDelivery>= 1) {
             $validator->errors()->add('purchase_order', 'Are you sure want to create new supplier delivery?');
         }
     }

    public function action(Agent|Supplier $parent, array $objectData, bool $force = false): SupplierDelivery
    {
        $this->parent = $parent;
        $this->force  = $force;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }
}
