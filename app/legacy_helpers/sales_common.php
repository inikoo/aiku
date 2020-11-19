<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 21:36:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Sales\TaxBand;

function get_legacy_to_delete_transactions($parent) {
    return [
        'ProductHistoricVariation' => $parent->transactions->whereIn('transaction_type', 'Product')->pluck('id', 'transaction_id')->all(),
        'ShippingZone'             => $parent->transactions->whereIn('transaction_type', 'ShippingZone')->pluck('id', 'transaction_id')->all(),
        'Charge'                   => $parent->transactions->whereIn('transaction_type', 'Charge')->pluck('id', 'transaction_id')->all(),
        'Adjust'                   => $parent->transactions->whereIn('transaction_type', 'Adjust')->pluck('id', 'transaction_id')->all()

    ];
}

function get_legacy_tax_band($transaction_type, $legacy_tax_code) {
    $taxBand = (new TaxBand)->firstwhere('code', $legacy_tax_code);
    if ($taxBand) {
        return $taxBand->id;
    } else {
        if ($transaction_type != 'Adjust') {
            print "\n   tax_code: ".$legacy_tax_code."\n";
            exit;
        }
    }

    return null;
}

function get_legacy_transaction_data($store_id, $onptf_data) {


    switch ($onptf_data->{'Transaction Type'}) {
        case 'Shipping':
            $transaction_type = 'ShippingZone';
            $transaction_id   = get_legacy_shipping_transaction_id($onptf_data->{'Transaction Type Key'});
            break;
        case 'Charges':
            $transaction_type = 'Charge';
            $transaction_id   = get_legacy_charges_transaction_id($onptf_data->{'Transaction Type Key'});
            break;
        case 'Insurance':
            $transaction_type = 'Charge';
            $transaction_id   = get_legacy_type_charges_transaction_id('insurance', $store_id);
            break;
        case 'Premium':
            $transaction_type = 'Charge';
            $transaction_id   = get_legacy_type_charges_transaction_id('premium', $store_id);
            break;
        case 'Adjust':
            $transaction_type = 'Adjust';
            $transaction_id   = get_legacy_type_adjusts_transaction_id('legacy', $store_id);
            break;
        case 'Credit':
            $transaction_type = 'Adjust';
            $transaction_id   = get_legacy_type_adjusts_transaction_id('credit', $store_id);
            break;
        case 'Refund':
            $transaction_type = 'Adjust';
            $transaction_id   = get_legacy_type_adjusts_transaction_id('refund', $store_id);
            break;
        default:
            print "\n ".$onptf_data->{'Order No Product Transaction Fact Key'}."  transaction type : ".$onptf_data->{'Transaction Type'}."\n";

            exit();
    }


    return [
        'type'        => $transaction_type,
        'id'          => $transaction_id,
        'tax_band_id' => get_legacy_tax_band($transaction_type, strtolower($onptf_data->{'Tax Category Code'}))

    ];
}
