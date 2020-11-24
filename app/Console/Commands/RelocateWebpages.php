<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Mon, 23 Nov 2020 14:12:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;


use App\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateWebpages extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:webpages {--tenant=*}';
    protected $description = 'Relocate legacy webpages';


    public function handle() {
        $this->tenant = Tenant::current();

        if (Arr::get($this->tenant->data, 'legacy')) {
            print ('Relocation webpages '.$this->tenant->slug."\n");
            $this->set_legacy_connection($this->tenant->data['legacy']['db']);

            $this->relocate_block("from `Website Dimension`", 'relocate_websites',100,'quiet');
            $this->relocate_block("from `Page Store Dimension`", 'relocate_webpages',1000);




        }

        return 0;
    }


}



