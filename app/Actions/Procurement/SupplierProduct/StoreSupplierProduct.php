<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 18:00:17 Malaysia Time, Bali Airport
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierProduct;

use App\Actions\Procurement\Agent\Hydrators\AgentHydrateSuppliers;
use App\Actions\Procurement\HistoricSupplierProduct\StoreHistoricSupplierProduct;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateSupplierProducts;
use App\Actions\Procurement\SupplierProduct\Hydrators\SupplierProductHydrateUniversalSearch;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProcurement;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreSupplierProduct
{
    use AsAction;
    use WithAttributes;

    public int $hydratorsDelay = 0;
    public bool $skipHistoric  = false;

    public function handle(Supplier $supplier, array $modelData): SupplierProduct
    {
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
        AgentHydrateSuppliers::dispatchIf($supplierProduct->agent_id, $supplierProduct->agent)->delay($this->hydratorsDelay);
        SupplierProductHydrateUniversalSearch::dispatch($supplierProduct);


        GroupHydrateProcurement::dispatch($supplier->group)->delay($this->hydratorsDelay);

        return $supplierProduct;
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'unique:supplier_products', 'between:2,9', 'alpha'],
            'name'        => ['required', 'max:250', 'string'],
            'cost'        => ['required'],
            'source_id'   => ['sometimes', 'nullable', 'string'],
            'source_slug' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function action(Supplier $supplier, array $modelData, bool $skipHistoric = false): SupplierProduct
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($supplier, $validatedData, $skipHistoric);
    }

    public function asFetch(
        Supplier $supplier,
        array $modelData,
        int $hydratorsDelay = 60,
        bool $skipHistoric = false,
    ): SupplierProduct {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->skipHistoric   = $skipHistoric;

        return $this->handle($supplier, $modelData);
    }
}
