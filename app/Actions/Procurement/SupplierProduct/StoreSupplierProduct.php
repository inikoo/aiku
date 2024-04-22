<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 18:00:17 Malaysia Time, Bali Airport
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierProduct;

use App\Actions\GrpAction;
use App\Actions\Procurement\HistoricSupplierProduct\StoreHistoricSupplierProduct;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateSupplierProducts;
use App\Actions\Procurement\SupplierProduct\Hydrators\SupplierProductHydrateUniversalSearch;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydrateSupplierProducts;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProductSuppliers;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use App\Rules\AlphaDashDotSpaceSlashParenthesis;
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


        /** @var SupplierProduct $supplierProduct */
        $supplierProduct = $supplier->products()->create($modelData);

        $supplierProduct->stats()->create();

        if (!$this->skipHistoric) {
            $historicProduct = StoreHistoricSupplierProduct::run($supplierProduct);
            $supplierProduct->update(
                [
                    'current_historic_supplier_product_id' => $historicProduct->id
                ]
            );
        }

        SupplierHydrateSupplierProducts::dispatch($supplier)->delay($this->hydratorsDelay);
        AgentHydrateSupplierProducts::dispatchIf($supplierProduct->agent_id, $supplierProduct->agent)->delay($this->hydratorsDelay);
        SupplierProductHydrateUniversalSearch::dispatch($supplierProduct);


        GroupHydrateProductSuppliers::dispatch($supplier->group)->delay($this->hydratorsDelay);

        return $supplierProduct;
    }

    public function rules(): array
    {
        return [
            'code'                   => [
                'required',
                'max:64',
                new AlphaDashDotSpaceSlashParenthesis(),
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'supplier_products',
                    extraConditions: [
                        ['column' => 'supplier_id', 'value' => $this->supplier_id],
                    ]
                ),

            ],
            'name'                   => ['required', 'string', 'max:255'],
            'cost'                   => ['required'],
            'source_id'              => ['sometimes', 'nullable', 'string'],
            'source_slug'            => ['sometimes', 'nullable', 'string'],
            'source_slug_inter_org'  => ['sometimes', 'nullable', 'string'],
            'source_organisation_id' => ['sometimes', 'nullable'],
        ];
    }

    public function action(Supplier $supplier, array $modelData, bool $skipHistoric = false, int $hydratorsDelay = 0): SupplierProduct
    {
        $this->supplier_id    = $supplier->id;
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->skipHistoric   = $skipHistoric;

        $this->initialisation($supplier->group, $modelData);

        return $this->handle($supplier, $this->validatedData);
    }


}
