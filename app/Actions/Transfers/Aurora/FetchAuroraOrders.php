<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:49:09 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Helpers\Attachment\SaveModelAttachment;
use App\Actions\Ordering\Order\StoreOrder;
use App\Actions\Ordering\Order\UpdateOrder;
use App\Enums\Ordering\Transaction\TransactionTypeEnum;
use App\Models\Accounting\Payment;
use App\Models\Ordering\Order;
use App\Transfers\Aurora\WithAuroraAttachments;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraOrders extends FetchAuroraAction
{
    use WithAuroraAttachments;

    public string $commandSignature = 'fetch:orders {organisations?*} {--S|shop= : Shop slug}  {--s|source_id=} {--d|db_suffix=} {--w|with=* : Accepted values: transactions payments attachments full} {--N|only_new : Fetch only new} {--d|db_suffix=} {--r|reset}';

    private bool $errorReported=false;

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId, bool $forceWithTransactions = false): ?Order
    {
        if ($orderData = $organisationSource->fetchOrder($organisationSourceId)) {
            $order = $this->processFetchOrder($organisationSource, $orderData);


            if (!$order) {

                if(!$this->errorReported) {
                    $this->recordFetchError($organisationSource, $orderData, 'Order', 'fetching');
                }

                return null;
            }

            if (in_array('transactions', $this->with) or in_array('full', $this->with) or $forceWithTransactions) {
                $this->fetchTransactions($organisationSource, $order);
            }
            if (in_array('payments', $this->with) or in_array('full_todo', $this->with)) {
                $this->fetchPayments($organisationSource, $order);
            }
            $this->updateAurora($order);

            $sourceData = explode(':', $order->source_id);


            if (in_array('full', $this->with)) {
                foreach (
                    DB::connection('aurora')
                        ->table('Delivery Note Dimension')
                        ->where('Delivery Note Order Key', $sourceData[1])
                        ->select('Delivery Note Key as source_id')
                        ->orderBy('source_id')->get() as $deliveryNote
                ) {
                    FetchAuroraDeliveryNotes::run($organisationSource, $deliveryNote->source_id, true);
                }
            }

            if (in_array('full', $this->with)) {
                foreach (
                    DB::connection('aurora')
                        ->table('Invoice Dimension')
                        ->where('Invoice Order Key', $sourceData[1])
                        ->select('Invoice Key as source_id')
                        ->orderBy('source_id')->get() as $invoice
                ) {
                    FetchAuroraInvoices::run($organisationSource, $invoice->source_id, true);
                }
            }


            return $order;
        }

        return null;
    }

    private function processFetchOrder($organisationSource, $orderData): ?Order
    {
        $order = null;
        if (!empty($orderData['order']['source_id']) and $order = Order::withTrashed()->where('source_id', $orderData['order']['source_id'])->first()) {
            try {
                $order = UpdateOrder::make()->action(order: $order, modelData: ['order'], strict: false, audit: false);
            } catch (Exception $e) {
                $this->recordError($organisationSource, $e, $orderData['order'], 'Order', 'update');
                $this->errorReported=true;
            }
        } elseif ($orderData['parent']) {
            try {
                $order = StoreOrder::make()->action(
                    parent: $orderData['parent'],
                    modelData: $orderData['order'],
                    strict: false,
                    hydratorsDelay: $this->hydrateDelay
                );
            } catch (Exception $e) {
                $this->recordError($organisationSource, $e, $orderData['order'], 'Order', 'store');
                $this->errorReported=true;
                return null;
            }
        }

        if (in_array('attachments', $this->with)) {
            $sourceData = explode(':', $order->source_id);
            foreach ($this->parseAttachments($sourceData[1]) ?? [] as $attachmentData) {
                SaveModelAttachment::run(
                    $order,
                    $attachmentData['fileData'],
                    $attachmentData['modelData'],
                );
                $attachmentData['temporaryDirectory']->delete();
            }
        }


        return $order;
    }


    private function parseAttachments($staffKey): array
    {
        $attachments = $this->getModelAttachmentsCollection(
            'Order',
            $staffKey
        )->map(function ($auroraAttachment) {
            return $this->fetchAttachment($auroraAttachment);
        });

        return $attachments->toArray();
    }

    private function fetchPayments($organisationSource, Order $order): void
    {
        $paymentsToDelete = $order->payments()->pluck('source_id')->all();
        foreach (
            DB::connection('aurora')
                ->table('Order Payment Bridge')
                ->select('Payment Key')
                ->where('Order Key', $order->source_id)
                ->get() as $auroraData
        ) {
            $payment = $this->parseOrderPayment($organisationSource, $auroraData->{'Payment Key'});


            if (!in_array($payment->id, $paymentsToDelete)) {
                $order->payments()->attach(
                    $payment->id,
                    [
                        'amount' => $payment->amount,
                        'share'  => 1
                    ]
                );
            }

            $paymentsToDelete = array_diff($paymentsToDelete, [$auroraData->{'Payment Key'}]);
        }

        $order->payments()->whereIn('id', $paymentsToDelete)->delete();
    }


    public function parseOrderPayment($organisationSource, $source_id): Payment
    {
        $payment = Payment::withTrashed()->where('source_id', $source_id)->first();
        if (!$payment) {
            $payment = FetchAuroraPayments::run($organisationSource, $source_id);
        }

        return $payment;
    }


    private function fetchTransactions($organisationSource, Order $order): void
    {
        $transactionsToDelete = $order->transactions()->where('type', TransactionTypeEnum::ORDER)->pluck('source_id', 'id')->all();


        $sourceData = explode(':', $order->source_id);
        foreach (
            DB::connection('aurora')
                ->table('Order Transaction Fact')
                ->select('Order Transaction Fact Key')
                ->where('Order Transaction Type', 'Order')
                ->where('Order Key', $sourceData[1])
                ->get() as $auroraData
        ) {
            $transactionsToDelete = array_diff($transactionsToDelete, [$organisationSource->getOrganisation()->id.':'.$auroraData->{'Order Transaction Fact Key'}]);
            FetchTransactions::run($organisationSource, $auroraData->{'Order Transaction Fact Key'}, $order);
        }
        $order->transactions()->whereIn('id', array_keys($transactionsToDelete))->forceDelete();
    }

    public function updateAurora(Order $order): void
    {
        $sourceData = explode(':', $order->source_id);
        DB::connection('aurora')->table('Order Dimension')
            ->where('Order Key', $sourceData[1])
            ->update(['aiku_id' => $order->id]);
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Order Dimension')
            ->select('Order Key as source_id')
            ->where('Order State', '!=', 'InBasket');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }
        $query->orderBy('Order Date');

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Order Dimension');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Order Dimension')->update(['aiku_id' => null]);
    }

}
