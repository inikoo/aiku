<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 19-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Procurement\PurchaseOrder\Search;

use App\Actions\HydrateModel;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Support\Collection;

class ReindexPurchaseOrderSearch extends HydrateModel
{
    public string $commandSignature = 'purchase_order:search {organisations?*} {--s|slugs=} ';


    public function handle(PurchaseOrder $purchaseOrder): void
    {
        PurchaseOrderRecordSearch::run($purchaseOrder);
    }

    protected function getModel(string $slug): PurchaseOrder
    {
        return PurchaseOrder::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return PurchaseOrder::all();
    }
}
