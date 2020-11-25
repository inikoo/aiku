<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 21 Nov 2020 14:09:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;


use App\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateMailshots extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:mailshots {--tenant=*}';
    protected $description = 'Relocate legacy mailshots';


    public function handle() {
        $this->tenant = Tenant::current();

        if (Arr::get($this->tenant->data, 'legacy')) {
            print ('Relocation Mailshots '.$this->tenant->slug."\n");
            $this->set_legacy_connection($this->tenant->data['legacy']['db']);


            $this->relocate_block("from `Email Campaign Type Dimension`", 'relocate_email_services',100,'quiet');
            $this->relocate_block("from `Website Dimension`", 'relocate_websites',100,'quiet');
            $this->relocate_block("from `Email Campaign Dimension`", 'relocate_mailshots');
            $this->relocate_block("from `Email Template Dimension`", 'relocate_email_template');
            $this->relocate_block("from `Published Email Template Dimension`", 'relocate_published_email_template');
            $this->relocate_block("from `Email Tracking Dimension`", 'relocate_email_tracking', 1000);

        }

        return 0;
    }


}



