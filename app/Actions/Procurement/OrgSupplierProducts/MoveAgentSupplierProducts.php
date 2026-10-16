<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\SupplyChain\SupplierProduct\MoveSupplierProductsToSupplier;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Enums\SupplyChain\SupplierProduct\SupplierProductStateEnum;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;

/**
 * An agent moves products from a generic record to their real factory: picked one by one, or a whole family at once.
 */
class MoveAgentSupplierProducts extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementEditAuthorisation;

    /**
     * @return array{moved: int, skipped: array<int, string>}
     */
    public function handle(Supplier $factory, array $modelData): array
    {
        return MoveSupplierProductsToSupplier::run($factory, $this->supplierProducts($factory, $modelData));
    }

    /**
     * @return Collection<int, SupplierProduct>
     */
    private function supplierProducts(Supplier $factory, array $modelData): Collection
    {
        $query = SupplierProduct::where('agent_id', $factory->agent_id)->with('supplier', 'orgSupplierProducts.orgSupplier');

        if ($ids = Arr::get($modelData, 'supplier_product_ids')) {
            return $query->whereIn('id', $ids)->get();
        }

        $family = Arr::get($modelData, 'stock_family_id');

        return $query
            ->where('supplier_id', Arr::get($modelData, 'from_supplier_id'))
            ->whereIn('state', [SupplierProductStateEnum::ACTIVE, SupplierProductStateEnum::IN_PROCESS])
            ->where(fn ($query) => $family === 'none'
                ? $query->whereDoesntHave('stocks', fn ($stocks) => $stocks->whereNotNull('stock_family_id'))
                : $query->whereHas('stocks', fn ($stocks) => $stocks->where('stock_family_id', (int) $family)))
            ->get();
    }

    public function rules(): array
    {
        return [
            'supplier_product_ids'   => ['required_without:from_supplier_id', 'array'],
            'supplier_product_ids.*' => ['integer'],
            'from_supplier_id'       => ['required_without:supplier_product_ids', 'integer'],
            'stock_family_id'        => ['required_with:from_supplier_id'],
        ];
    }

    public function asController(Organisation $organisation, Supplier $supplier, ActionRequest $request): array
    {
        $this->initialisation($organisation, $request);
        abort_unless($this->organisationAgentSupplies($supplier), 404);

        return $this->handle($supplier, $this->validatedData);
    }
}
