<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 12:32:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;

use App\Models\Sales\Order;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

/**
 * Class RelocateOrders
 *
 * @property Tenant $tenant
 *
 * @package App\Console\Commands
 */
class RelocateOrders extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:orders {--tenant=*}';
    protected $description = 'Relocate legacy orders';

    public function handle(): int {

        DB::disableQueryLog();
        $this->tenant = Tenant::current();

        $legacy_orders_table = '`Order Dimension`';
        if (Arr::get($this->tenant->data, 'legacy')) {

            $this->set_legacy_connection($this->tenant->data['legacy']['db']);


            print ('Relocation orders from '.$this->tenant->slug." ".$this->tenant->data['legacy']['db']."  \n");

            $count_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_orders_table, [])[0];
            $bar        = $this->output->createProgressBar($count_data->num);
            $bar->setFormat('debug');
            $bar->start();
            $max   = 500;
            $total = $count_data->num;
            $pages = ceil($total / $max);
            for ($i = 1; $i < ($pages + 1); $i++) {
                $offset = (($i - 1) * $max);

                $sql = "* from `Order Dimension` limit ?,?";
                foreach (DB::connection('legacy')->select("select $sql", [
                    $offset,
                    $max
                ]
                ) as $legacy_data
                ) {
                    if ($legacy_data->{'Order State'} != 'InBasket') {
                        relocate_order( $legacy_data);
                    }else{
                        $order = (new Order)->firstWhere('legacy_id', $legacy_data->{'Order Key'});
                        if($order){
                            delete_relocated_order($order);
                        }

                    }
                    $bar->advance();
                }
            }
            $bar->finish();
            print "\n";

        }

        return 0;
    }
}
