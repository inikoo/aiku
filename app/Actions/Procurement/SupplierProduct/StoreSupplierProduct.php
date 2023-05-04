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
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreSupplierProduct
{
    use AsAction;
    use WithAttributes;

    public function handle(Supplier $supplier, array $modelData, bool $skipHistoric = false): SupplierProduct
    {
        /** @var SupplierProduct $supplierProduct */

        $supplierProduct = $supplier->products()->create($modelData);

        $supplierProduct->stats()->create();

        if (!$skipHistoric) {
            $historicProduct = StoreHistoricSupplierProduct::run($supplierProduct);
            $supplierProduct->update(
                [
                    'current_historic_supplier_product_id' => $historicProduct->id
                ]
            );
        }

        SupplierHydrateSupplierProducts::dispatch($supplier);
        AgentHydrateSuppliers::dispatchIf($supplierProduct->agent_id, $supplierProduct->agent);

        SupplierProductHydrateUniversalSearch::dispatch($supplierProduct);
        return $supplierProduct;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:group.supplier_products', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'cost' => ['required'],
        ];
    }

    public function action(Supplier $supplier, array $objectData, bool $skipHistoric = false): SupplierProduct
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($supplier, $validatedData, $skipHistoric);
    }
}
