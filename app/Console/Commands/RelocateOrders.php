<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 12:32:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;
use App\Models\CRM\Customer;
use App\Models\CRM\CustomerClient;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateOrders extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:orders {--tenant=*}';
    protected $description = 'Relocate legacy orders';

    public function handle() {

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

                $sql = "* from `Order Dimension` where limit ?,?";
                foreach (DB::connection('legacy')->select("select $sql", [
                    $offset,
                    $max
                ]
                ) as $legacy_data
                ) {
                    if ($legacy_data->{'Order State'} != 'InBasket') {

                        if ($legacy_data->{'Order Customer Client Key'}) {
                            $parent = CustomerClient::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Order Customer Client Key'});
                        } else {
                            $parent = Customer::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Order Customer Key'});
                        }

                        $order = relocate_order($parent, $legacy_data);

                        $sql = "* from `Delivery Note Dimension` where `Delivery Note Order Key`=?";
                        foreach (DB::connection('legacy')->select("select $sql", [$order->legacy_id]) as $dn_legacy_data) {

                            if ($dn_legacy_data->{'Delivery Note State'} != 'Cancelled to Restock') {
                                $delivery_note = relocate_delivery_note($dn_legacy_data, $order);

                                if ($dn_legacy_data->{'Delivery Note State'} == 'Dispatched' or $dn_legacy_data->{'Delivery Note State'} == 'Cancelled') {
                                    $delivery_note->syncItems(get_legacy_dispatched_itf($delivery_note), 'delivery_note_items');
                                } else {
                                    $delivery_note->syncItems(get_legacy_picking_itf($delivery_note), 'pickings');
                                }

                            } else {
                                relocate_return($dn_legacy_data, $order);
                            }
                        }

                        $sql = "* from `Invoice Dimension` where `Invoice Order Key`=?";
                        foreach (DB::connection('legacy')->select("select $sql", [$order->legacy_id]) as $invoice_legacy_data) {
                            if ($invoice_legacy_data->{'Invoice Type'} != 'CreditNote') {
                                relocate_invoice($invoice_legacy_data, $order);
                            }
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
