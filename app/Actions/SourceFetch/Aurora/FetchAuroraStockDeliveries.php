<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Procurement\StockDelivery\StoreStockDelivery;
use App\Actions\Procurement\StockDelivery\UpdateStockDelivery;
use App\Actions\Studio\Attachment\SaveModelAttachment;
use App\Models\Procurement\StockDelivery;
use App\Services\Organisation\Aurora\WithAuroraAttachments;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraStockDeliveries extends FetchAuroraAction
{
    use WithAuroraAttachments;

    public string $commandSignature = 'fetch:stock-deliveries {organisations?*} {--s|source_id=} {--w|with=* : Accepted values: attachments}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?StockDelivery
    {
        if ($stockDeliveryData = $organisationSource->fetchStockDelivery($organisationSourceId)) {
            if (!empty($stockDeliveryData['stockDelivery']['source_id']) and $stockDelivery = StockDelivery::withTrashed()->where('source_id', $stockDeliveryData['stockDelivery']['source_id'])
                    ->first()) {
                $stockDelivery = UpdateStockDelivery::run($stockDelivery, $stockDeliveryData['stockDelivery']);



                //  $this->fetchTransactions($organisationSource, $stockDelivery);
                $this->updateAurora($stockDelivery);

                $this->setAttachments($stockDelivery);
                return $stockDelivery;
            } else {
                if ($stockDeliveryData['parent']) {
                    $stockDelivery = StoreStockDelivery::run($stockDeliveryData['parent'], $stockDeliveryData['stockDelivery'], $stockDeliveryData['delivery_address']);
                    //  $this->fetchTransactions($organisationSource, $stockDelivery);
                    $this->updateAurora($stockDelivery);

                    $this->setAttachments($stockDelivery);
                    return $stockDelivery;
                }
                print "Warning Supplier Delivery $organisationSourceId do not have parent\n";
            }
        } else {
            print "Warning error fetching order $organisationSourceId\n";
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
            'Supplier Delivery',
            $staffKey
        )->map(function ($auroraAttachment) {return $this->fetchAttachment($auroraAttachment);});
        return $attachments->toArray();
    }

    /*

    private function fetchTransactions($organisationSource, $order): void
    {
        $transactionsToDelete = $order->transactions()->where('type', TransactionTypeEnum::ORDER)->pluck('source_id', 'id')->all();
        foreach (
            DB::connection('aurora')
                ->table('Order Transaction Fact')
                ->select('Order Transaction Fact Key')
                ->where('Order Transaction Type', 'Order')
                ->where('Order Key', $order->source_id)
                ->get() as $auroraData
        ) {
            $transactionsToDelete = array_diff($transactionsToDelete, [$auroraData->{'Order Transaction Fact Key'}]);
            FetchTransactions::run($organisationSource, $auroraData->{'Order Transaction Fact Key'}, $order);
        }
        $order->transactions()->whereIn('id', array_keys($transactionsToDelete))->delete();
    }
    */

    public function updateAurora(StockDelivery $StockDelivery): void
    {
        DB::connection('aurora')->table('Supplier Delivery Dimension')
            ->where('Supplier Delivery Key', $StockDelivery->source_id)
            ->update(['aiku_id' => $StockDelivery->id]);
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Supplier Delivery Dimension')
            ->select('Supplier Delivery Key as source_id');
        $query->orderBy('Supplier Delivery Date');

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Supplier Delivery Dimension');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }
}
