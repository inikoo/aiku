<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 11:05
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\SupplyChain\PackingSpec;

use App\Models\Procurement\OrgSupplierProduct;
use Illuminate\Support\Collection;

trait WithPackingCopyTargets
{
    /**
     * Other products of the same supplier the packing may be copied to: supplied by the same agent when an agent saves
     * it, of the same organisation when purchasing saves it.
     *
     * @return Collection<int, OrgSupplierProduct>
     */
    protected function copyTargets(OrgSupplierProduct $orgSupplierProduct, array $ids): Collection
    {
        $supplierId = $orgSupplierProduct->supplierProduct->supplier_id;
        if (!$ids || !$supplierId) {
            return collect();
        }

        $agent = $this->getOrganisationAgent($this->organisation);

        return OrgSupplierProduct::whereIn('id', $ids)
            ->where('id', '!=', $orgSupplierProduct->id)
            ->where('supplier_product_id', '!=', $orgSupplierProduct->supplier_product_id)
            ->whereHas('supplierProduct', fn ($query) => $query->where('supplier_id', $supplierId))
            ->when(
                $agent,
                fn ($query) => $query->whereHas('orgAgent', fn ($query) => $query->where('agent_id', $agent->id)),
                fn ($query) => $query->where('organisation_id', $this->organisation->id)
            )
            ->with('supplierProduct')
            ->get()
            ->unique('supplier_product_id')
            ->values();
    }
}
