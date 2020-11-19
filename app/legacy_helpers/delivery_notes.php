<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:34:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Distribution\DeliveryNote;
use App\Models\Distribution\Shipper;
use App\Models\Distribution\Stock;
use Illuminate\Support\Facades\DB;

function get_legacy_picking_itf($delivery_note) {

    $itf_table = '`Inventory Transaction Fact`';
    $itf_where = '`Delivery Note key`';

    $legacy_picking_itf = [];

    foreach (
        DB::connection('legacy')->select(
            "select * from  $itf_table where   $itf_where=?", [$delivery_note->legacy_id]
        ) as $legacy_picking_itf_data
    ) {


        $stock     = Stock::withTrashed()->firstWhere('legacy_id', $legacy_picking_itf_data->{'Part SKU'});
        $picked_by = null;
        $picked    = null;
        $picked_at = null;
        $locations = [];

        switch ($delivery_note->state) {
            case 'labeled':

                $state  = 'done';
                $status = 'packed';


                $qty = abs($legacy_picking_itf_data->{'Inventory Transaction Quantity'});

                if ($qty < $legacy_picking_itf_data->{'Required'}) {


                    if ($qty == 0) {
                        $status = 'out_of_stock';
                    } else {
                        $status = 'partially_packed';
                    }

                }

                $picked_by = $delivery_note->picker_id;
                $picked    = $qty * $stock->packed_in;
                $locations = [
                    $legacy_picking_itf_data->{'Location Key'} => $picked
                ];
                if ($legacy_picking_itf_data->{'Date Picked'} != '' and $legacy_picking_itf_data->{'Date Picked'} != '0000-00-00 00:00:00') {
                    $picked_at = $legacy_picking_itf_data->{'Date Picked'};
                }

                break;
            case 'waiting':
                $state  = 'created';
                $status = 'processing';
                break;
            default:
                print "== ".$delivery_note->state."\n";
                exit;

        }


        $data = [
            'location' => $locations
        ];


        $legacy_picking_itf[$stock->id] = [

            'weight'    => $legacy_picking_itf_data->{'Inventory Transaction Weight'},
            'required'  => $legacy_picking_itf_data->{'Required'} * $stock->packed_in,
            'picked'    => $picked,
            'picked_by' => $picked_by,
            'picked_at' => $picked_at,
            'legacy_id' => $legacy_picking_itf_data->{'Inventory Transaction Key'},
            'state'     => $state,
            'status'    => $status,
            'data'      => $data,
            'tenant_id' => $delivery_note->tenant_id

        ];

    }

    return $legacy_picking_itf;

}

function get_legacy_dispatched_itf($delivery_note) {

    $itf_table = '`Inventory Transaction Fact`';
    $itf_where = '`Delivery Note Key`';

    $legacy_dispatched_itf = [];


    foreach (
        DB::connection('legacy')->select(
            "select * from  $itf_table where   $itf_where=?", [$delivery_note->legacy_id]
        ) as $legacy_picking_itf_data
    ) {


        $stock = Stock::withTrashed()->firstWhere('legacy_id', $legacy_picking_itf_data->{'Part SKU'});
        $qty   = abs($legacy_picking_itf_data->{'Inventory Transaction Quantity'});

        $was_dispatched = true;

        $status = 'dispatched';


        if ($qty == 0) {
            $was_dispatched = false;
            $status         = 'out_of_stock';
        } elseif ($qty < $legacy_picking_itf_data->{'Required'}) {
            $status = 'partially_dispatched';
        }

        if ($delivery_note->state == 'cancelled') {
            $status = 'cancelled';
        }

        $data = [];

        $legacy_dispatched_itf[$stock->id] = [
            'was_dispatched' => $was_dispatched,
            'status'         => $status,
            'weight'         => $legacy_picking_itf_data->{'Inventory Transaction Weight'},
            'required'       => $legacy_picking_itf_data->{'Required'} * $stock->packed_in,
            'dispatched'     => $qty * $stock->packed_in,
            'legacy_id'      => $legacy_picking_itf_data->{'Inventory Transaction Key'},
            'data'           => $data,
            'tenant_id'      => $delivery_note->tenant_id
        ];

    }


    return $legacy_dispatched_itf;

}


function get_state_from_legacy_delivery_note($legacyState) {
    $status = 'processing';
    $state  = null;
    switch ($legacyState) {
        case 'Ready to be Picked':
            $state = 'waiting';
            break;
        case 'Picker Assigned':
            $state = 'assigned';
            break;
        case 'Picking':
            $state = 'picking';
            break;
        case 'Picked':
            $state = 'picked';
            break;
        case 'Packing':
            $state = 'packing';
            break;
        case 'Packed':
            $state = 'packed';
            break;
        case 'Packed Done':
        case 'Approved':
            $state = 'labeled';
            break;
        case 'Dispatched':
            $state  = 'dispatched';
            $status = 'invoiced';
            break;
        case 'Cancelled':
            $state  = 'cancelled';
            $status = 'cancelled';
            break;

    }

    return [
        $status,
        $state
    ];
}


function relocate_delivery_note($legacy_data, $order) {


    $order_data = fill_legacy_data(
        [

        ], $legacy_data
    );


    list($status, $state) = get_state_from_legacy_delivery_note($legacy_data->{'Delivery Note State'});

    //enum('Replacement & Shortages','Order','Replacement','Shortages','Sample','Donation')

    switch ($legacy_data->{'Delivery Note Type'}) {
        case 'Order':
            $type               = 'purchase';
            $order_submitted_at = $order->submitted_at;
            break;
        case 'Sample':
            $type               = 'sample';
            $order_submitted_at = $order->submitted_at;
            break;
        case 'Donation':
            $type               = 'donation';
            $order_submitted_at = $order->submitted_at;
            break;
        default:
            $type               = 'replacement';
            $order_submitted_at = $legacy_data->{'Delivery Note Date Created'};

    }

    $shipper_id = null;
    $shipper    = (new Shipper)->firstWhere('legacy_id', $legacy_data->{'Delivery Note Shipper Key'});
    if ($shipper) {
        $shipper_id = $shipper->id;
    }

    $dispatched_at = null;
    $cancelled_at  = null;
    switch ($legacy_data->{'Delivery Note State'}) {


        case 'Dispatched':

            $dispatched_at = $legacy_data->{'Delivery Note Date Dispatched'};

            break;
        case 'Cancelled':

            $cancelled_at = $legacy_data->{'Delivery Note Date Cancelled'};
            $shipper_id   = null;


    }

    $picker_id = get_employee_id_from_legacy($legacy_data->{'Delivery Note Assigned Picker Key'});
    $packer_id = get_employee_id_from_legacy($legacy_data->{'Delivery Note Assigned Packer Key'});


    $delivery_note = (new DeliveryNote)->updateOrCreate(
        [
            'legacy_id' => $legacy_data->{'Delivery Note Key'},

        ], [
            'tenant_id' => $$order->tenant_id,
            'order_id'  => $order->id,
            'type'      => $type,
            'number'    => $legacy_data->{'Delivery Note ID'},


            'weight' => $legacy_data->{'Delivery Note Weight'},


            'state'      => $state,
            'status'     => $status,
            'date'       => $legacy_data->{'Delivery Note Date'},
            'picker_id'  => $picker_id,
            'packer_id'  => $packer_id,
            'shipper_id' => $shipper_id,


            'data'               => $order_data,
            'created_at'         => $legacy_data->{'Delivery Note Date Created'},
            'order_submitted_at' => $order_submitted_at,
            'dispatched_at'      => $dispatched_at,
            'cancelled_at'       => $cancelled_at,


        ]
    );


    $delivery_address = process_legacy_immutable_address('Delivery Note', '', $legacy_data);

    $delivery_note->addresses()->syncWithoutDetaching([$delivery_address->id => ['scope' => 'delivery']]);

    $delivery_note->delivery_address_id = $delivery_address->id;

    //enum('Ready to be Picked','Picker Assigned','Picking','Picked','Packing','Packed','Packed Done','Approved','Dispatched','Cancelled','Cancelled to Restock')


    $delivery_note->save();

    return $delivery_note;
}

function relocate_return($dn_legacy_data, $order) {

}
