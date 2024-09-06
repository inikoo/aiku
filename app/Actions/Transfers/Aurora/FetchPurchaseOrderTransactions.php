<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 14:28:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Procurement\PurchaseOrderTransaction\StorePurchaseOrderTransaction;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Transfers\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchPurchaseOrderTransactions
{
    use AsAction;


    public function handle(SourceOrganisationService $organisationSource, int $source_id, PurchaseOrder $purchaseOrder): ?PurchaseOrderTransaction
    {
        if ($transactionData = $organisationSource->fetchPurchaseOrderTransaction(id: $source_id, purchaseOrder: $purchaseOrder)) {
            if (!PurchaseOrderTransaction::where('source_id', $transactionData['purchase_order_transaction']['source_id'])
                ->first()) {
                $transaction = StorePurchaseOrderTransaction::make()->action(
                    purchaseOrder: $purchaseOrder,
                    historicSupplierProduct: $transactionData['historic_supplier_product'],
                    modelData: $transactionData['purchase_order_transaction'],
                    strict: false
                );

                $sourceData = explode(':', $transaction->source_id);
                DB::connection('aurora')->table('Purchase Order Transaction Fact')
                    ->where('Purchase Order Transaction Fact Key', $sourceData[1])
                    ->update(['aiku_id' => $transaction->id]);

                return $transaction;
            }
        }


        return null;
    }
}
