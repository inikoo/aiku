<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\StockDelivery;

use App\Actions\OrgAction;
use App\Actions\Procurement\StockDelivery\Traits\HasStockDeliveryHydrators;
use App\Enums\Procurement\StockDelivery\StockDeliveryStateEnum;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgPartner;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\StockDelivery;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StoreStockDelivery extends OrgAction
{
    use HasStockDeliveryHydrators;


    private OrgSupplier|OrgAgent|OrgPartner $parent;
    private bool $force;

    public function handle(Organisation $organisation, OrgSupplier|OrgAgent|OrgPartner $parent, array $modelData): StockDelivery
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

        data_set($modelData, 'parent_code', $parent->code, false);
        data_set($modelData, 'parent_name', $parent->name, false);

        /** @var StockDelivery $stockDelivery */
        $stockDelivery = $parent->stockDeliveries()->create($modelData);

        $this->runHydrators($stockDelivery);

        return $stockDelivery;
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
            'number'      => [
                'sometimes',
                'required',
                $this->strict ? 'alpha_dash' : 'string',
                $this->strict ? new IUnique(
                    table: 'stock_deliveries',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                    ]
                ) : null,
            ],
            'date'        => ['required', 'date'],
            'parent_code' => ['sometimes', 'required', 'string', 'max:256'],
            'parent_name' => ['sometimes', 'required', 'string', 'max:256'],
            'source_id'   => ['sometimes', 'required', 'string', 'max:64'],

        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $stockDelivery = $this->parent->stockDeliveries()->where('state', StockDeliveryStateEnum::CREATING)->count();

        if (!$this->force && $stockDelivery >= 1) {
            $validator->errors()->add('stock_delivery', 'Are you sure want to create new supplier delivery?');
        }
    }

    public function action(Organisation $organisation, OrgSupplier|OrgAgent|OrgPartner $parent, array $modelData): StockDelivery
    {
        $this->asAction = true;
        $this->parent   = $parent;
        $this->force    = true;

        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $parent, $modelData);
    }
}
