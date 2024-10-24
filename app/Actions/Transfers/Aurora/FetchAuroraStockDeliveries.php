<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Procurement\StockDelivery\StoreStockDelivery;
use App\Actions\Procurement\StockDelivery\UpdateStockDelivery;
use App\Models\Procurement\StockDelivery;
use App\Transfers\Aurora\WithAuroraAttachments;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraStockDeliveries extends FetchAuroraAction
{
    use WithAuroraAttachments;

    public string $commandSignature = 'fetch:stock-deliveries {organisations?*} {--s|source_id=} {--d|db_suffix=} {--N|only_new : Fetch only new} {--w|with=* : Accepted values: transactions}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?StockDelivery
    {
        if ($stockDeliveryData = $organisationSource->fetchStockDelivery($organisationSourceId)) {

            if (empty($stockDeliveryData['org_parent'])) {
                print "Warning Supplier Delivery $organisationSourceId do not have parent, skipping\n";

                return null;
            }

            if ($stockDelivery = StockDelivery::withTrashed()->where('source_id', $stockDeliveryData['stockDelivery']['source_id'])->first()) {
                try {
                    $stockDelivery = UpdateStockDelivery::make()->action(
                        $stockDelivery,
                        $stockDeliveryData['stockDelivery'],
                        hydratorsDelay: 60,
                        strict: false,
                        audit: false
                    );
                    $this->recordChange($organisationSource, $stockDelivery->wasChanged());
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $stockDeliveryData['stockDelivery'], 'StockDelivery', 'update');

                    return null;
                }
            } else {
                try {
                    $stockDelivery = StoreStockDelivery::make()->action(
                        $stockDeliveryData['org_parent'],
                        $stockDeliveryData['stockDelivery'],
                        hydratorsDelay: 60,
                        strict: false,
                        audit: false
                    );

                    StockDelivery::enableAuditing();
                    $this->saveMigrationHistory(
                        $stockDelivery,
                        Arr::except($stockDeliveryData['stockDelivery'], ['fetched_at', 'last_fetched_at', 'source_id'])
                    );

                    $this->recordNew($organisationSource);

                    $sourceData = explode(':', $stockDelivery->source_id);
                    DB::connection('aurora')->table('Supplier Delivery Dimension')
                        ->where('Supplier Delivery Key', $sourceData[1])
                        ->update(['aiku_id' => $stockDelivery->id]);
                } catch (Exception|Throwable $e) {
                    $this->recordError($organisationSource, $e, $stockDeliveryData['stockDelivery'], 'StockDelivery', 'store');

                    return null;
                }
            }


            $this->setAttachments($stockDelivery);

            if (in_array('transactions', $this->with)) {
                $this->fetchTransactions($organisationSource, $stockDelivery);
            }



            return $stockDelivery;
        }

        return null;
    }

    private function setAttachments($stockDelivery): void
    {
        $this->processFetchAttachments($stockDelivery, 'Supplier Delivery');
    }



    private function fetchTransactions($organisationSource, StockDelivery $stockDelivery): void
    {
        //        $transactionsToDelete = $stockDelivery->transactions()->where('type', TransactionTypeEnum::ORDER)->pluck('source_id', 'id')->all();
        //        foreach (
        //            DB::connection('aurora')
        //                ->table('Order Transaction Fact')
        //                ->select('Order Transaction Fact Key')
        //                ->where('Order Key', $order->source_id)
        //                ->get() as $auroraData
        //        ) {
        //            $transactionsToDelete = array_diff($transactionsToDelete, [$auroraData->{'Order Transaction Fact Key'}]);
        //            FetchAuroraTransactions::run($organisationSource, $auroraData->{'Order Transaction Fact Key'}, $order);
        //        }
        //        $order->transactions()->whereIn('id', array_keys($transactionsToDelete))->delete();
    }



    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Supplier Delivery Dimension')
            ->select('Supplier Delivery Key as source_id');
        $query->orderBy('Supplier Delivery Date');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

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
