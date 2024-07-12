<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Models\Accounting\Invoice;
use App\Models\Helpers\TaxCategory;
use Illuminate\Support\Facades\DB;

class FetchAuroraInvoiceTransaction extends FetchAurora
{
    protected function parseInvoiceTransaction(Invoice $invoice, bool $isFulfilment): void
    {
        if ($this->auroraModelData->{'Product Key'}) {
            $transaction = $invoice->customer->transactions()->where('source_id', $this->organisation->id.':'.$this->auroraModelData->{'Order Transaction Fact Key'})->first();

            if ($transaction) {
                $this->parsedData['model'] = $transaction;
            } else {
                $historicAsset = $this->parseHistoricAsset(
                    $this->organisation,
                    $this->auroraModelData->{'Product Key'}
                );

                $this->parsedData['model'] = $historicAsset;
            }

            if (!$isFulfilment) {
                $order = $this->parseOrder($this->organisation->id.':'.$this->auroraModelData->{'Order Key'});

                if (!$order) {
                    print "Order not found >".$this->auroraModelData->{'Order Transaction Fact Key'}."\n";
                }
                $orderId = $order?->id;
            } else {
                $orderId = null;
            }


            $taxCategory = TaxCategory::where('source_id', $this->auroraModelData->{'Order Transaction Tax Category Key'})
                ->firstOrFail();

            $this->parsedData['transaction'] = [
                'order_id'        => $orderId,
                'tax_category_id' => $taxCategory->id,
                'quantity'        => $this->auroraModelData->{'Delivery Note Quantity'},

                'gross_amount' => $this->auroraModelData->{'Order Transaction Gross Amount'},
                'net_amount'   => $this->auroraModelData->{'Order Transaction Amount'},
                'grp_exchange' => $invoice->grp_exchange,
                'org_exchange' => $invoice->org_exchange,

                'source_id' => $this->organisation->id.':'.$this->auroraModelData->{'Order Transaction Fact Key'},

            ];
        } else {
            print "Warning Asset Key missing in transaction >".$this->auroraModelData->{'Order Transaction Fact Key'}."\n";
        }
    }

    public function fetchInvoiceTransaction(int $id, Invoice $invoice, bool $isFulfilment): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        if ($this->auroraModelData) {
            $this->parseInvoiceTransaction($invoice, $isFulfilment);
        }

        return $this->parsedData;
    }

    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order Transaction Fact')
            ->where('Order Transaction Fact Key', $id)->first();
    }
}
