<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sun, 18 Oct 2020 22:51:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;

use App\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateTenants extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:tenants {--tenant=*}';

    protected $description = 'Migrate legacy accounts';

    public function handle() {


        $this->tenant = Tenant::current();
        if (Arr::exists($this->tenant->data, 'legacy')) {
            print ('Relocation tenant '.$this->tenant->slug."\n");


            $table = '`Account Dimension`';
            $this->set_legacy_connection($this->tenant->data['legacy']['db']);

            foreach (DB::connection('legacy')->select('select * from '.$table, []) as $legacy_data) {

                print_r($legacy_data);
                $data = $this->tenant->data;


                $this->tenant->data   = $data;
                $this->tenant->save();


            }
        }

        return 0;


    }


}
