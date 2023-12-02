<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDelivery;

use App\Actions\Procurement\SupplierDelivery\Traits\HasHydrators;
use App\Models\Grouping\Organisation;
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
    use HasHydrators;

    private bool $force;

    private Supplier|Agent $parent;

    public function handle(Organisation $organisation, Agent|Supplier $parent, array $modelData): SupplierDelivery
    {
        data_set($modelData, 'organisation_id', $organisation->id);
        /** @var SupplierDelivery $supplierDelivery */
        $supplierDelivery = $parent->supplierDeliveries()->create($modelData);

        $this->getHydrators($supplierDelivery);

        return $supplierDelivery;
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'numeric', 'unique:supplier_deliveries,number'],
            'date'   => ['required', 'date']
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $supplierDelivery = $this->parent->SupplierDeliveries()->count();

        if (!$this->force && $supplierDelivery >= 1) {
            $validator->errors()->add('supplier_delivery', 'Are you sure want to create new supplier delivery?');
        }
    }

    public function action(Organisation $organisation, Agent|Supplier $parent, array $objectData, bool $force = false): SupplierDelivery
    {
        $this->parent = $parent;
        $this->force  = $force;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($organisation, $parent, $validatedData);
    }
}
