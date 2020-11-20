<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 18:25:03 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateCustomers extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:customers {--tenant=*}';
    protected $description = 'Relocate legacy customers';


    public function handle() {
        $this->tenant = Tenant::current();

        if (Arr::get($this->tenant->data, 'legacy')) {


            $this->set_legacy_connection($this->tenant->data['legacy']['db']);


            print ('Relocation customers from '.$this->tenant->slug."\n");

            $sql = "count(*) as num from `Customer Dimension`";

            $count_customers_data = DB::connection('legacy')->select("select $sql", [])[0];

            $bar = $this->output->createProgressBar($count_customers_data->num);
            $bar->setFormat('debug');
            $bar->start();
            $max   = 1000;
            $total = $count_customers_data->num;
            $pages = ceil($total / $max);
            for ($i = 1; $i < ($pages + 1); $i++) {
                $offset = (($i - 1) * $max);

                $sql = "* from `Customer Dimension` limit ?,?";
                foreach (
                    DB::connection('legacy')->select(
                        "select $sql", [
                                         $offset,
                                         $max
                                     ]
                    ) as $legacy_data
                ) {

                    relocate_customer($this->tenant, $legacy_data);

                    $bar->advance();
                }
            }


            $bar->finish();


            print ('Relocation deleted customers from '.$this->tenant->slug."\n");

            $sql = "count(*) as num from `Customer Deleted Dimension`";

            $count_deleted_customers_data = DB::connection('legacy')->select("select $sql", [])[0];


            $bar = $this->output->createProgressBar($count_deleted_customers_data->num);
            $bar->setFormat('debug');
            $bar->start();

            $sql = "* from `Customer Deleted Dimension`";
            foreach (DB::connection('legacy')->select("select $sql", []) as $raw_legacy_data) {

                if (!$raw_legacy_data->{'Customer Key'}) {
                    continue;
                }
                if ($raw_legacy_data->{'Customer Deleted Metadata'} == '') {
                    continue;
                }

                $legacy_data = json_decode(gzuncompress($raw_legacy_data->{'Customer Deleted Metadata'}));


                $customer = relocate_customer($this->tenant, $legacy_data);

                $customer->status     = 'deleted';
                $customer->state      = 'deleted';
                $customer->deleted_at = $raw_legacy_data->{'Customer Deleted Date'};
                $customer->save();


                $bar->advance();
            }

            $bar->finish();
            print "\n";


            print ('Relocation prospects from '.$this->tenant->slug."\n");

            $sql = "count(*) as num from `Prospect Dimension`";

            $count_prospects_data = DB::connection('legacy')->select("select $sql", [])[0];
            $bar                  = $this->output->createProgressBar($count_prospects_data->num);
            $bar->setFormat('debug');
            $bar->start();
            $max   = 500;
            $total = $count_prospects_data->num;
            $pages = ceil($total / $max);
            for ($i = 1; $i < ($pages + 1); $i++) {
                $offset = (($i - 1) * $max);
                $sql    = "* from `Prospect Dimension` limit ?,?";
                foreach (
                    DB::connection('legacy')->select(
                        "select $sql", [
                                         $offset,
                                         $max
                                     ]
                    ) as $legacy_data
                ) {
                    relocate_prospect($this->tenant, $legacy_data);
                    $bar->advance();
                }
            }
            $bar->finish();
            print "\n";

        }


        return 0;


    }

}
