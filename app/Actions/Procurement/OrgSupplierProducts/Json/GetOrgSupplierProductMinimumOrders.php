<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 13:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts\Json;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\Procurement\OrgSupplierProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;

class GetOrgSupplierProductMinimumOrders extends OrgAction
{
    use WithProcurementEditAuthorisation;

    public function asController(OrgSupplierProduct $orgSupplierProduct, ActionRequest $request): Collection
    {
        $this->initialisation($orgSupplierProduct->organisation, $request);

        return $this->handle($orgSupplierProduct);
    }

    /**
     * The product and every other active supplier of the same SKO in this organisation, since a minimum
     * is the supplier's and holds for every organisation buying from it.
     *
     * @return Collection<int, object{id: int, code: string, name: string, supplier_name: ?string, minimum_carton_order: ?int}>
     */
    public function handle(OrgSupplierProduct $orgSupplierProduct): Collection
    {
        $orgStockIds = DB::table('org_stock_has_org_supplier_products')
            ->where('org_supplier_product_id', $orgSupplierProduct->id)
            ->pluck('org_stock_id');

        return DB::table('org_supplier_products as osp')
            ->join('supplier_products as sp', 'sp.id', 'osp.supplier_product_id')
            ->leftJoin('suppliers as sup', 'sup.id', 'sp.supplier_id')
            ->where(fn ($query) => $query->where('osp.id', $orgSupplierProduct->id)
                ->orWhere(fn ($siblings) => $siblings
                    ->where('osp.organisation_id', $orgSupplierProduct->organisation_id)
                    ->where('osp.state', 'active')
                    ->whereIn('osp.id', DB::table('org_stock_has_org_supplier_products')->whereIn('org_stock_id', $orgStockIds)->select('org_supplier_product_id'))))
            ->orderByRaw('osp.id = ? desc', [$orgSupplierProduct->id])
            ->orderBy('sup.name')
            ->get(['osp.id', 'sp.code', 'sp.name', 'sup.name as supplier_name', 'sp.minimum_carton_order']);
    }
}
