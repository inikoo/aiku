<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 12:32:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;


use App\Tenant;
use App\Models\Distribution\DeliveryNote;
use Illuminate\Console\Command;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;


class SyncPickingFirestore extends Command {

    use TenantAware;

    protected $signature = 'sync:picking {--tenant=*}';
    protected $description = 'Sync to Firestore (picking orders)';


    public function __construct() {
        parent::__construct();
    }


    public function handle() {
        //$tenant = Tenant::current();


        $firestore = app('firebase.firestore')->database('delivery_notes');

        $collectionReference = $firestore->collection('delivery_notes');

        //$documentReference = $collectionReference->document('test');
        //$snapshot = $documentReference->snapshot();



        $delivery_notes = (new DeliveryNote)->where('status', 'processing')->get();

        foreach ($delivery_notes as $delivery_note) {
            echo $delivery_note->number."\n";


            $documentData=[
                'number' => $delivery_note->number,
                'created_at'=>$delivery_note->created_at->toDateTimeString() ,
                'store' => [
                        'id'=>$delivery_note->store_id,
                        'slug'=>$delivery_note->store->slug,
                ],
                'customer' => [
                    'id'=>$delivery_note->customer_id,
                    'slug'=>$delivery_note->customer->name,
                ]
            ];
            print_r(
                $documentData
            );
            exit;

            $deliveryNoteRef = $firestore->collection('delivery_notes')->document($tenant->slug.'.'.$delivery_note->id);
            $deliveryNoteRef->set($documentData, ['merge' => true]);

            exit;

        }

      //  print_r($snapshot);
        return 0;
    }


}
