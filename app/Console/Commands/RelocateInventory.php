<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 18:50:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;
use App\Models\Distribution\Location;
use App\Models\Distribution\Stock;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateInventory extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:inventory {--tenant=*}';
    protected $description = 'Relocate legacy inventory (stock)';


    public function __construct() {
        parent::__construct();
    }

    public function handle() {
        $tenant = Tenant::current();

        $legacy_stocks_table          = '`Part Dimension`';
        $legacy_deleted_stocks_table  = '`Part Deleted Dimension`';
        $legacy_location_stocks_table = '`Part Location Dimension`';


        if (Arr::get($tenant->data, 'legacy')) {


            $this->set_legacy_connection($tenant->data['legacy']['db']);



            print ('Relocation inventory from '.$tenant->subdomain."\n");
            $count_stocks_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_stocks_table, [])[0];
            $bar               = $this->output->createProgressBar($count_stocks_data->num);
            $bar->setFormat('debug');

            $bar->start();
            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_stocks_table, []) as $legacy_data) {
                $stock = $this->relocate_inventory($legacy_data, $tenant);

                $location_stock_data = [];
                foreach (DB::connection('legacy')->select("select * from".' '.$legacy_location_stocks_table.' where `Part SKU`=?', [$stock->legacy_id]) as $legacy_part_location_data) {

                    $location                           = (new Location)->firstWhere('legacy_id', $legacy_part_location_data->{'Location Key'});
                    $location_stock_data[$location->id] = ['quantity' => $stock->packed_in * $legacy_part_location_data->{'Quantity On Hand'}];

                }

                $stock->locations()->sync($location_stock_data);

                $bar->advance();
            }
            $bar->finish();
            print "\n";

            print ('Relocation deleted parts from '.$tenant->subdomain."\n");


            $count_stocks_data = DB::connection('legacy')->select("select count(*) as num from".' '.$legacy_deleted_stocks_table, [])[0];
            $bar               = $this->output->createProgressBar($count_stocks_data->num);
            $bar->setFormat('debug');

            $bar->start();
            foreach (DB::connection('legacy')->select("select * from".' '.$legacy_deleted_stocks_table, []) as $raw_legacy_data) {


                $legacy_data = json_decode(gzuncompress($raw_legacy_data->{'Part Deleted Metadata'}));
                if ($legacy_data) {


                    $stock             = $this->relocate_inventory($legacy_data, $tenant);
                    $stock->deleted_at = $raw_legacy_data->{'Part Deleted Date'};
                    $stock->save();
                }

                $bar->advance();
            }
            $bar->finish();
            print "\n";


        }


        return 0;


    }


    function relocate_inventory($legacy_data, $tenant) {

        $stock_data = $this->fill_data(
            [
                'package.description' => 'Part Package Description',
                'package.weight'      => 'Part Package Weight',
                'package.dimensions'  => 'Part Package Dimensions',
                'package.weight'      => 'Part Unit Weight',
            ], $legacy_data
        );


        if ($package_dimensions = json_decode($legacy_data->{'Part Package Dimensions'})) {
            Arr::set($stock_data, 'package.dimensions', $package_dimensions);
        }

        if ($unit_dimensions = json_decode($legacy_data->{'Part Unit Dimensions'})) {
            Arr::set($stock_data, 'unit.dimensions', $unit_dimensions);
        }


        $stock_settings = $this->fill_data(
            [], $legacy_data
        );

        $legacy_status_to_state = [
            'In Use'        => 'active',
            'Discontinuing' => 'discontinuing',
            'In Process'    => 'creating',
            'Not In Use'    => 'discontinued'
        ];

        $state = $legacy_status_to_state[$legacy_data->{'Part Status'}];


        $quantity_status = strtolower($legacy_data->{'Part Stock Status'});
        if ($quantity_status == 'out_of_stock') {
            $quantity_status = 'outOfStock';
        }


        if ($legacy_data->{'Part Valid From'} == '0000-00-00 00:00:00') {
            $created_at = null;
        } else {
            $created_at = $legacy_data->{'Part Valid From'};
        }

        if ($legacy_data->{'Part Valid To'} == '0000-00-00 00:00:00' or $legacy_data->{'Part Valid From'} == '') {
            $deleted_at = gmdate('Y-m-d H:i:s');
        } else {
            $deleted_at = $legacy_data->{'Part Valid To'};
        }

        $code = $legacy_data->{'Part Reference'};

        if ($code == '') {
            $code = 'empty_'.$legacy_data->{'Part SKU'};
        }

        $barcode ='';
        if(isset($legacy_data->{'Part SKO Barcode'})) {
            $barcode = $legacy_data->{'Part SKO Barcode'};
        }
        if ($barcode == '') {
            $barcode = $legacy_data->{'Part Barcode Number'};

        }
        if ($barcode == '') {
            $barcode =null;
        }


        if (isset($legacy_data->{'Part Recommended Product Unit Name'})) {
            $unit_description = $legacy_data->{'Part Recommended Product Unit Name'};

        } elseif (isset($legacy_data->{'Part Unit Description'})) {
            $unit_description = $legacy_data->{'Part Unit Description'};
        }else{

            $unit_description = 'empty_'.$legacy_data->{'Part SKU'};

        }

        if(isset($legacy_data->{'Part Unit Label'})){
            $unit_label=$legacy_data->{'Part Unit Label'};

        }else{
            $unit_label='piece';

        }


        return Stock::withTrashed()->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Part SKU'},
            ], [
                'tenant_id'          => $tenant->id,
                'code'               => $code,
                'barcode'            => ($barcode == '' ? null : $barcode),
                'description'        => $unit_description,
                'quantity_status'    => $quantity_status,
                'available_forecast' => $legacy_data->{'Part Days Available Forecast'},
                'packed_in'          => $legacy_data->{'Part Units Per Package'},
                'unit_type'          => $unit_label,
                'state'              => $state,
                'unit_quantity'      => $legacy_data->{'Part Current On Hand Stock'} * $legacy_data->{'Part Units Per Package'},
                'data'               => $stock_data,
                'settings'           => $stock_settings,
                'created_at'         => $created_at,
                'deleted_at'         => ($state == 'discontinued' ? $deleted_at : null),

            ]
        );
    }


}
