<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 18:50:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;
use App\Models\Distribution\Location;
use App\Models\Distribution\Shipper;
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

        $legacy_deleted_locations_table = '`Location Deleted Dimension`';

        if (Arr::get($tenant->data, 'legacy')) {


            $this->set_legacy_connection($tenant->data['legacy']['db']);


            $legacy_shippers_table = '`Shipper Dimension`';
            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_shippers_table.'   ', []) as $legacy_data) {
                $this->relocate_shippers($legacy_data, $tenant);
            }

            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_warehouses_table, []) as $legacy_data) {
                $this->relocate_warehouse($legacy_data, $tenant);

            }


            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_warehouse_areas_table, []) as $legacy_data) {
                $this->relocate_warehouse_area($legacy_data, $tenant);

            }


            print ('Relocation locations from '.$tenant->subdomain."\n");
            $count_deleted_locations_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_deleted_locations_table, [])[0];
            $count_locations_data         = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_locations_table, [])[0];


            $bar = $this->output->createProgressBar($count_deleted_locations_data->num + $count_locations_data->num);


            $bar->setFormat('debug');
            $bar->start();


            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_locations_table, []) as $legacy_data) {
                $this->relocate_location($legacy_data, $tenant);
                $bar->advance();
            }

            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_deleted_locations_table, []) as $legacy_deleted_data) {

                $legacy_data                  = json_decode($legacy_deleted_data->{'Location Deleted Metadata'});
                $deleted_location             = $this->relocate_location($legacy_data, $tenant);
                $deleted_location->deleted_at = $legacy_deleted_data->{'Location Deleted Date'};
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

        //Hack to avoid error in legacy locations

        /*
        $warehouse_legacy_id=$legacy_data->{'Location Warehouse Key'};
        if ($legacy_data->{'Location Warehouse Key'} > 1) {
            $warehouse_legacy_id=1;
        }
        */
        $warehouse_legacy_id=1;
        $location_data = $this->fill_data(
            [], $legacy_data
        );


        $warehouse = (new Warehouse)->firstWhere('legacy_id', $warehouse_legacy_id);


        if ($warehouse_area = (new WarehouseArea)->firstWhere('legacy_id', $legacy_data->{'Location Warehouse Area Key'})) {
            $warehouse_area_id = $warehouse_area->id;
        } else {
            $warehouse_area_id = null;
        }


        return  Location::withTrashed()->updateOrCreate(
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

    function relocate_shippers($legacy_data, $tenant) {


        $shipper_data = $this->fill_data(
            [
                'company'     => 'Shipper Name',
                'website'     => 'Shipper Website',
                'tracking_ul' => 'Shipper Tracking URL',
                'api_id'      => 'Shipper API Key'

            ], $legacy_data
        );
        $shipper_data = array_filter($shipper_data);

        return (new Shipper)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Shipper Key'},

            ], [
                'tenant_id' => $tenant->id,

                'code'   => $legacy_data->{'Shipper Code'},
                'status' => strtolower($legacy_data->{'Shipper Status'}),
                'data'   => $shipper_data,


            ]
        );


    }



}
