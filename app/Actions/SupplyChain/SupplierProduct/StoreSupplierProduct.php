<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Aug 2024 14:46:37 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierProduct;

use App\Actions\GrpAction;
use App\Actions\Procurement\HistoricSupplierProduct\StoreHistoricSupplierProduct;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydrateSupplierProducts;
use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydrateSupplierProducts;
use App\Actions\SupplyChain\SupplierProduct\Search\SupplierProductRecordSearch;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProductSuppliers;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateSupplierProducts;
use App\Enums\SupplyChain\SupplierProduct\SupplierProductStateEnum;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use App\Rules\AlphaDashDotSpaceSlashParenthesisPlus;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreSupplierProduct extends GrpAction
{
    public bool $skipHistoric = false;
    private int $supplier_id;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("procurement.".$this->group->id.".edit");
    }

    public function handle(Supplier $supplier, array $modelData): SupplierProduct
    {
        data_set($modelData, 'group_id', $supplier->group_id);

        if ($supplier->agent_id) {
            $modelData['agent_id'] = $supplier->agent_id;
        }
        data_set($modelData, 'currency_id', $supplier->currency_id);


        /** @var SupplierProduct $supplierProduct */
        $supplierProduct = $supplier->supplierProducts()->create($modelData);
        $supplierProduct->refresh();
        $supplierProduct->stats()->create();

        if (!$this->skipHistoric) {
            $historicProduct = StoreHistoricSupplierProduct::run($supplierProduct);
            $supplierProduct->update(
                [
                    'current_historic_supplier_product_id' => $historicProduct->id
                ]
            );
        }

        GroupHydrateSupplierProducts::dispatch($supplier->group)->delay($this->hydratorsDelay);
        SupplierHydrateSupplierProducts::dispatch($supplier)->delay($this->hydratorsDelay);
        AgentHydrateSupplierProducts::dispatchIf((bool)$supplierProduct->agent_id, $supplierProduct->agent)->delay($this->hydratorsDelay);
        SupplierProductRecordSearch::dispatch($supplierProduct);


        GroupHydrateProductSuppliers::dispatch($supplier->group)->delay($this->hydratorsDelay);

        return $supplierProduct;
    }

    public function rules(): array
    {
        return [
            'code'                   => [
                'required',
                $this->strict ? 'max:64' : 'max:255',
                $this->strict ? new AlphaDashDotSpaceSlashParenthesisPlus() : 'string',
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'supplier_products',
                    extraConditions: [
                        ['column' => 'supplier_id', 'value' => $this->supplier_id],
                    ]
                ),

            ],
            'name'                   => ['required', 'string', 'max:255'],
            'state'                  => ['sometimes', 'required', Rule::enum(SupplierProductStateEnum::class)],
            'is_available'           => ['sometimes', 'required', 'boolean'],
            'cost'                   => ['required'],
            'source_id'              => ['sometimes', 'nullable', 'string'],
            'source_slug'            => ['sometimes', 'nullable', 'string'],
            'source_slug_inter_org'  => ['sometimes', 'nullable', 'string'],
            'source_organisation_id' => ['sometimes', 'nullable'],
        ];
    }


    public function action(Supplier $supplier, array $modelData, bool $skipHistoric = false, int $hydratorsDelay = 0, bool $strict = true): SupplierProduct
    {
        $this->supplier_id    = $supplier->id;
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->skipHistoric   = $skipHistoric;
        $this->strict         = $strict;

        $this->initialisation($supplier->group, $modelData);

        return $this->handle($supplier, $this->validatedData);
    }


}
