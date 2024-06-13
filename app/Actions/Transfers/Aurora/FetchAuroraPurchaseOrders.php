<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 17 Apr 2023 17:11:07 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Helpers\Attachment\SaveModelAttachment;
use App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrder;
use App\Models\Procurement\PurchaseOrder;
use App\Transfers\Aurora\WithAuroraAttachments;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraPurchaseOrders extends FetchAuroraAction
{
    use WithAuroraAttachments;

    public string $commandSignature = 'fetch:purchase-orders {organisations?*} {--s|source_id=} {--d|db_suffix=} {--N|only_new : Fetch only new} {--r|reset} {--w|with=* : Accepted values: attachments}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?PurchaseOrder
    {
        if ($purchaseOrderData = $organisationSource->fetchPurchaseOrder($organisationSourceId)) {

            if (!empty($purchaseOrderData['purchase_order']['source_id']) and $purchaseOrder = PurchaseOrder::withTrashed()->where('source_id', $purchaseOrderData['purchase_order']['source_id'])->first()) {
                $purchaseOrder = UpdatePurchaseOrder::make()->action(
                    purchaseOrder: $purchaseOrder,
                    modelData: $purchaseOrderData['purchase_order'],
                    strict: false
                );



                //  $this->fetchTransactions($organisationSource, $purchaseOrder);
                $this->updateAurora($purchaseOrder);
                $this->setAttachments($purchaseOrder);

                return $purchaseOrder;
            } else {
                if ($purchaseOrderData['org_parent']) {
                    //  try {
                    $purchaseOrder = StorePurchaseOrder::make()->action(
                        organisation: $organisationSource->organisation,
                        parent: $purchaseOrderData['org_parent'],
                        modelData: $purchaseOrderData['purchase_order'],
                        strict: false
                    );
                    //  } catch (Exception $e) {
                    //      dd($e);
                    //      $this->recordError($organisationSource, $e, $purchaseOrderData['purchase_order'], 'PurchaseOrder', 'store');
                    //      return null;
                    //  }

                    $this->updateAurora($purchaseOrder);

                    $this->setAttachments($purchaseOrder);
                    return $purchaseOrder;
                }
                print "Warning purchase order ".$purchaseOrderData['purchase_order']['number']."  Id:$organisationSourceId do not have parent\n";
                dd($purchaseOrderData);
            }
        }

        return null;
    }

    private function setAttachments($stockDelivery): void
    {
        if (in_array('attachments', $this->with)) {
            $sourceData= explode(':', $stockDelivery->source_id);
            foreach ($this->parseAttachments($sourceData[1]) ?? [] as $attachmentData) {

                SaveModelAttachment::run(
                    $stockDelivery,
                    $attachmentData['fileData'],
                    $attachmentData['modelData'],
                );
                $attachmentData['temporaryDirectory']->delete();
            }
        }
    }

    private function parseAttachments($staffKey): array
    {
        $attachments            = $this->getModelAttachmentsCollection(
            'Purchase Order',
            $staffKey
        )->map(function ($auroraAttachment) {return $this->fetchAttachment($auroraAttachment);});
        return $attachments->toArray();
    }

    /*

    private function fetchTransactions($organisationSource, $purchaseOrder): void
    {
        $transactionsToDelete = $purchaseOrder->transactions()->where('type', TransactionTypeEnum::ORDER)->pluck('source_id', 'id')->all();
        foreach (
            DB::connection('aurora')
                ->table('Order Transaction Fact')
                ->select('Order Transaction Fact Key')
                ->where('Order Transaction Type', 'Order')
                ->where('Order Key', $purchaseOrder->source_id)
                ->get() as $auroraData
        ) {
            $transactionsToDelete = array_diff($transactionsToDelete, [$auroraData->{'Order Transaction Fact Key'}]);
            FetchTransactions::run($organisationSource, $auroraData->{'Order Transaction Fact Key'}, $purchaseOrder);
        }
        $purchaseOrder->transactions()->whereIn('id', array_keys($transactionsToDelete))->delete();
    }
    */

    public function updateAurora(PurchaseOrder $purchaseOrder): void
    {
        $sourceData = explode(':', $purchaseOrder->source_id);

        DB::connection('aurora')->table('Purchase Order Dimension')
            ->where('Purchase Order Key', $sourceData[1])
            ->update(['aiku_id' => $purchaseOrder->id]);
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Purchase Order Dimension')
            ->select('Purchase Order Key as source_id')
            ->whereIn('Purchase Order Type', ['Parcel', 'Container']);
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        $query->orderBy('Purchase Order Date');

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Purchase Order Dimension')
            ->whereIn('Purchase Order Type', ['Parcel', 'Container']);

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Purchase Order Dimension')->update(['aiku_id' => null]);
    }
}
