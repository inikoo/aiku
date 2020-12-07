<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:33:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Sales\Invoice;
use App\Models\Sales\InvoiceTransaction;
use App\Models\Sales\OrderTransaction;
use App\Models\Stores\ProductHistoricVariation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;


function relocate_invoice_transactions(Invoice $invoice) {


    $toDelete = get_legacy_to_delete_transactions($invoice);


    $sql = "* from `Order Transaction Fact`  where `Invoice Key`=?";
    foreach (DB::connection('legacy')->select("select $sql", [$invoice->legacy_id]) as $otf_data) {

        $invoiceTransaction = (new InvoiceTransaction())->where('legacy_id', $otf_data->{'Order Transaction Fact Key'})->where('invoiceable_type', 'ProductHistoricVariation')->first();
        if ($invoiceTransaction) {

            unset($toDelete['ProductHistoricVariation'][$invoiceTransaction->transaction_id]);
            $invoiceTransaction->fill(
                [
                    'quantity'  => $otf_data->{'Delivery Note Quantity'},
                    'discounts' => $otf_data->{'Order Transaction Total Discount Amount'},
                    'net'       => $otf_data->{'Order Transaction Amount'},

                    'data' => []
                ]
            );
            $invoiceTransaction->save();
        } else {

            $product_historic_variant = (new ProductHistoricVariation())->firstWhere('legacy_id', $otf_data->{'Product Key'});

            unset($toDelete['ProductHistoricVariation'][$product_historic_variant->id]);

            $order_transaction = (new OrderTransaction())->where('legacy_id', $otf_data->{'Order Transaction Fact Key'})->where('transaction_type', 'ProductHistoricVariation')->first();



            $invoiceTransactions = new InvoiceTransaction(
                [
                    'order_transaction_id' => $order_transaction->id,
                    'invoice_id'           => $invoice->id,
                    'tenant_id'            => $invoice->tenant_id,
                    'quantity'             => $otf_data->{'Delivery Note Quantity'},
                    'discounts'            => $otf_data->{'Order Transaction Total Discount Amount'},
                    'net'                  => $otf_data->{'Order Transaction Amount'},
                    'legacy_id'            => $otf_data->{'Order Transaction Fact Key'},
                    'legacy_scope'         => 'otf',
                    'data'                 => []
                ]
            );



            $product_historic_variant->invoiceTransactions()->save($invoiceTransactions);
        }


    }

    $sql = "* from `Order No Product Transaction Fact` where  `Invoice Key` =?";
    foreach (DB::connection('legacy')->select("select $sql", [$invoice->legacy_id]) as $onptf_data) {

        $transaction_data = get_legacy_transaction_data($invoice->store_id, $onptf_data);

        $invoiceTransaction = (new InvoiceTransaction)->where('legacy_id', $onptf_data->{'Order No Product Transaction Fact Key'})->where('invoiceable_type', $transaction_data['type'])->first();
        if ($invoiceTransaction) {

            unset($toDelete[$transaction_data['type']][$invoiceTransaction->transaction_id]);

            $invoiceTransaction->fill(
                [
                    'quantity'    => 1,
                    'discounts'   => $onptf_data->{'Transaction Total Discount Amount'},
                    'net'         => $onptf_data->{'Transaction Net Amount'},
                    'tax_band_id' => $transaction_data['tax_band_id'],
                    'data'        => []
                ]
            );
            $invoiceTransaction->save();
        } else {

            unset($toDelete[$transaction_data['type']][$transaction_data['id']]);

            $order_transaction_id=null;
            $order_transaction = (new OrderTransaction())->where('legacy_id', $onptf_data->{'Order No Product Transaction Fact Key'})->where('transaction_type', $transaction_data['type'])->first();
            if($order_transaction){
                $order_transaction_id=$order_transaction->id;
            }

            $invoiceTransaction = new InvoiceTransaction(
                [
                    'order_transaction_id' => $order_transaction_id,
                    'invoice_id'           => $invoice->id,
                    'tenant_id'            => $invoice->tenant_id,
                    'invoiceable_type'     => $transaction_data['type'],
                    'invoiceable_id'       => $transaction_data['id'],
                    'quantity'             => 1,
                    'discounts'            => $onptf_data->{'Transaction Total Discount Amount'},
                    'net'                  => $onptf_data->{'Transaction Net Amount'},
                    'tax_band_id'          => $transaction_data['tax_band_id'],
                    'legacy_scope'         => 'onptf',
                    'legacy_id'            => $onptf_data->{'Order No Product Transaction Fact Key'},
                    'data'                 => []
                ]
            );
            $invoiceTransaction->save();
        }
    }

    InvoiceTransaction::destroy(Arr::flatten($toDelete));

}

function relocate_invoice($invoice_legacy_data, $order) {


    $invoice_data = fill_legacy_data(
        [
            'customerName'    => 'Invoice Customer Name',
            'customerContact' => 'Invoice Customer Contact Name',

            'currency' => 'Invoice Currency'
        ], $invoice_legacy_data
    );

    $type = 'invoice';
    if ($invoice_legacy_data->{'Invoice Type'} == 'Refund') {
        $type = 'refund';
    }

    $invoice = (new Invoice)->updateOrCreate(
        [
            'legacy_id' => $invoice_legacy_data->{'Invoice Key'},

        ], [
            'tenant_id'   => $order->tenant_id,
            'customer_id' => $order->customer_id,
            'type'        => $type,
            'number'      => $invoice_legacy_data->{'Invoice Public ID'},
            'exchange'    => $invoice_legacy_data->{'Invoice Currency Exchange'},
            'net'         => $invoice_legacy_data->{'Invoice Total Net Amount'},
            'total'       => $invoice_legacy_data->{'Invoice Total Amount'},
            'payment'     => (!$invoice_legacy_data->{'Invoice Payments Amount'}?0:$invoice_legacy_data->{'Invoice Payments Amount'}),
            'paid_at'     => $invoice_legacy_data->{'Invoice Paid Date'},


            'data'       => $invoice_data,
            'created_at' => $invoice_legacy_data->{'Invoice Date'},


        ]
    );

    $billing_address = process_legacy_immutable_address('Invoice', '', $invoice_legacy_data);


    $invoice->addresses()->syncWithoutDetaching([$billing_address->id => ['scope' => 'billing']]);

    $invoice->billing_address_id = $billing_address->id;

    $invoice->save();

    $invoice->orders()->syncWithoutDetaching([$order->id]);
    relocate_invoice_transactions($invoice);

    return $invoice;


}
