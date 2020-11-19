<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:33:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Sales\Invoice;

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
            'payment'     => $invoice_legacy_data->{'Invoice Payments Amount'},
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

    return $invoice;


}
