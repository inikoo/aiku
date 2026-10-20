<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\StockHasSupplierProduct;

use App\Actions\Inventory\OrgStockHasOrgSupplierProduct\SetOrgStockPreferredSupplierProduct;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithGoodsEditAuthorisation;
use App\Models\Goods\Stock;
use App\Models\Goods\StockHasSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class SetStockPreferredSupplierProduct extends OrgAction
{
    use WithGoodsEditAuthorisation;

    private const PREFERRED_PRIORITY = 10;

    public function handle(Stock $stock, SupplierProduct $supplierProduct): Stock
    {
        $link = StockHasSupplierProduct::where('stock_id', $stock->id)
            ->where('supplier_product_id', $supplierProduct->id)
            ->first();

        if (!$link) {
            throw ValidationException::withMessages([
                'supplier_product_id' => __('This supplier product is not linked to this master SKO.'),
            ]);
        }

        DB::transaction(function () use ($stock, $link) {
            StockHasSupplierProduct::where('stock_id', $stock->id)->update(['priority' => 0]);
            $link->update(['priority' => self::PREFERRED_PRIORITY]);

            $link->orgStockHasOrgSupplierProducts()
                ->where('status', true)
                ->with(['orgStock', 'orgSupplierProduct'])
                ->get()
                ->each(fn ($orgLink) => SetOrgStockPreferredSupplierProduct::make()->handle($orgLink->orgStock, $orgLink->orgSupplierProduct));
        });

        return $stock;
    }

    public function asController(Stock $stock, SupplierProduct $supplierProduct, ActionRequest $request): RedirectResponse
    {
        $this->initialisationFromGroup($stock->group, $request);
        $this->handle($stock, $supplierProduct);

        return back();
    }

    public function action(Stock $stock, SupplierProduct $supplierProduct): Stock
    {
        $this->asAction = true;
        $this->initialisationFromGroup($stock->group, []);

        return $this->handle($stock, $supplierProduct);
    }
}
