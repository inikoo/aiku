<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 12:32:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;


//use App\Tenant;
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

        $documentReference = $collectionReference->document('test');
        $snapshot = $documentReference->snapshot();


        print_r($snapshot);
        return 0;
    }


}
