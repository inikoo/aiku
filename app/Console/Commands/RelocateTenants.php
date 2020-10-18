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

    public function __construct() {
        parent::__construct();
    }


    public function handle() {


        $this->tenant = Tenant::current();
        if (Arr::get($this->tenant->data, 'legacy')) {
            print ('Relocation tenant '.$this->tenant->subdomain."\n");


            $table = '`Account Dimension`';
            $this->set_legacy_connection($this->tenant->data['legacy']['db']);

            foreach (DB::connection('legacy')->select('select * from '.$table, []) as $legacy_data) {

                $data    = $this->tenant->data;


                $country_translations = [
                    'ESP' => 'ES',
                    'GBR' => 'GB',
                    'SVK' => 'SK',
                    'IDN' => 'ID',

                ];



                $data['country_code'] = $country_translations[$legacy_data->{'Account Country Code'}];
                $this->tenant->data   = $data;
                $this->tenant->save();


            }
        }

        return 0;


    }


}
