<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDelivery;

use App\Actions\OrgAction;
use App\Actions\Procurement\SupplierDelivery\Traits\HasSupplierDeliveryHydrators;
use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStateEnum;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgPartner;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\SupplierDelivery;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StoreSupplierDelivery extends OrgAction
{
    use HasSupplierDeliveryHydrators;


    private OrgSupplier|OrgAgent|OrgPartner $parent;
    private bool $force;

    public function handle(Organisation $organisation, OrgSupplier|OrgAgent|OrgPartner $parent, array $modelData): SupplierDelivery
    {
        data_set($modelData, 'organisation_id', $organisation->id);
        data_set($modelData, 'group_id', $organisation->group_id);

        if (class_basename($parent) == 'OrgSupplier') {
            data_set($modelData, 'supplier_id', $parent->supplier_id);
        } elseif (class_basename($parent) == 'OrgAgent') {
            data_set($modelData, 'agent_id', $parent->agent_id);
        } elseif (class_basename($parent) == 'OrgPartner') {
            data_set($modelData, 'partner_id', $parent->organisation_id);
        }

        /** @var SupplierDelivery $supplierDelivery */
        $supplierDelivery = $parent->supplierDeliveries()->create($modelData);

        $this->runHydrators($supplierDelivery);

        return $supplierDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'number'          => [
                'sometimes',
                'required',
                $this->strict ? 'alpha_dash' : 'string',
                $this->strict ? new IUnique(
                    table: 'supplier_deliveries',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                    ]
                ) : null,
            ],
            'date'   => ['required', 'date']
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $supplierDelivery = $this->parent->supplierDeliveries()->where('state', SupplierDeliveryStateEnum::CREATING)->count();

        if (!$this->force && $supplierDelivery >= 1) {
            $validator->errors()->add('supplier_delivery', 'Are you sure want to create new supplier delivery?');
        }
    }

    public function action(Organisation $organisation, OrgSupplier|OrgAgent|OrgPartner $parent, array $modelData): SupplierDelivery
    {
        $this->asAction = true;
        $this->parent   = $parent;
        $this->force    = true;

        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $parent, $modelData);
    }
}
