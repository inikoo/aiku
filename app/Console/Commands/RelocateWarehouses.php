<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 18:50:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;
use App\Models\Distribution\Location;
use App\Models\Distribution\Warehouse;
use App\Models\Distribution\WarehouseArea;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateWarehouses extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:warehouses {--tenant=*}';
    protected $description = 'Relocate legacy warehouses, areas and locations';


    public function __construct() {
        parent::__construct();
    }


    public function handle() {
        $tenant = Tenant::current();

        $legacy_warehouses_table      = '`Warehouse Dimension`';
        $legacy_warehouse_areas_table = '`Warehouse Area Dimension`';
        $legacy_locations_table       = '`Location Dimension`';

        //$legacy_deleted_customers_table = '`Customer Deleted Dimension`';

        if (Arr::get($tenant->data, 'legacy')) {


            $this->set_legacy_connection($tenant->data['legacy']['db']);


            print ('Relocation warehouses from '.$tenant->subdomain."\n");
            $count_warehouses_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_warehouses_table, [])[0];
            $bar                   = $this->output->createProgressBar($count_warehouses_data->num);
            $bar->start();
            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_warehouses_table, []) as $legacy_data) {
                $this->relocate_warehouse($legacy_data, $tenant);
                $bar->advance();
            }
            $bar->finish();
            print "\n";

            print ('Relocation warehouse areas from '.$tenant->subdomain."\n");
            $count_warehouse_areas_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_warehouse_areas_table, [])[0];
            $bar                        = $this->output->createProgressBar($count_warehouse_areas_data->num);
            $bar->start();
            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_warehouse_areas_table, []) as $legacy_data) {
                $this->relocate_warehouse_area($legacy_data, $tenant);
                $bar->advance();
            }
            $bar->finish();
            print "\n";

            print ('Relocation locations from '.$tenant->subdomain."\n");
            $count_locations_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_locations_table, [])[0];
            $bar                  = $this->output->createProgressBar($count_locations_data->num);
            $bar->start();
            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_locations_table, []) as $legacy_data) {
                $this->relocate_location($legacy_data, $tenant);
                $bar->advance();
            }
            $bar->finish();
            print "\n";


        }


        return 0;


    }


    function relocate_warehouse($legacy_data, $tenant) {

        $warehouse_data = $this->fill_data(
            [], $legacy_data
        );

        $warehouse_settings = $this->fill_data(
            [], $legacy_data
        );

        return (new Warehouse)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Warehouse Key'},
            ], [
                'tenant_id'  => $tenant->id,
                'name'       => $legacy_data->{'Warehouse Name'},
                'data'       => $warehouse_data,
                'settings'   => $warehouse_settings,
                'created_at' => $legacy_data->{'Warehouse Valid From'},
            ]
        );
    }

    function relocate_warehouse_area($legacy_data, $tenant) {


        $warehouse_area_data = $this->fill_data(
            [], $legacy_data
        );


        if ($legacy_data->{'Warehouse Area Place'} == 'Local') {

            $warehouse = (new Warehouse)->firstWhere('legacy_id', $legacy_data->{'Warehouse Area Warehouse Key'});

            return (new WarehouseArea)->updateOrCreate(
                [
                    'legacy_id' => $legacy_data->{'Warehouse Area Key'},
                ], [
                    'tenant_id'    => $tenant->id,
                    'warehouse_id' => $warehouse->id,
                    'name'         => $legacy_data->{'Warehouse Area Name'},
                    'data'         => $warehouse_area_data,
                ]
            );
        } else {
            return (new Warehouse)->updateOrCreate(
                [
                    'legacy_id' => $legacy_data->{'Warehouse Area Key'},
                ], [
                    'tenant_id' => $tenant->id,
                    'name'      => $legacy_data->{'Warehouse Area Name'},
                    'data'      => $warehouse_area_data,
                ]
            );
        }


    }

    function relocate_location($legacy_data, $tenant) {

        $location_data = $this->fill_data(
            [], $legacy_data
        );

        $warehouse = (new Warehouse)->firstWhere('legacy_id', $legacy_data->{'Location Warehouse Key'});


        if ($warehouse_area = (new WarehouseArea)->firstWhere('legacy_id', $legacy_data->{'Location Warehouse Area Key'})) {
            $warehouse_area_id = $warehouse_area->id;
        } else {
            $warehouse_area_id = null;
        }


        return (new Location)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Location Key'},
            ], [
                'tenant_id'         => $tenant->id,
                'code'              => $legacy_data->{'Location Code'},
                'data'              => $location_data,
                'warehouse_area_id' => $warehouse_area_id,
                'warehouse_id'      => $warehouse->id

            ]
        );
    }

}
