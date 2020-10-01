<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 18:25:03 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;
use App\Models\HR\Employee;
use App\Models\System\Guest;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateEmployees extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:employees {--tenant=*}';
    protected $description = 'Relocate legacy employees';


    public function __construct() {
        parent::__construct();
    }


    public function handle() {
        $tenant = Tenant::current();

        $_table = '`Staff Dimension`';

        if (Arr::get($tenant->data, 'legacy')) {
            print ('Relocation staff from '.$tenant->subdomain." ".$tenant->data['legacy']['db']."  \n");

            $this->set_legacy_connection($tenant->data['legacy']['db']);

            foreach (DB::connection('legacy')->select("select * from".' '.$_table, []) as $legacy_data) {


                $employee_data = $this->fill_data(
                    [
                        'personal_identification' => 'Staff Official ID',
                        'next_of_kind.name'       => 'Staff Next of Kind',
                        'date_of_birth'           => 'Staff Birthday'
                    ], $legacy_data
                );


                if ($legacy_data->{'Staff Type'} == 'Employee') {
                    (new Employee)->firstOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'slug'      => Str::kebab($legacy_data->{'Staff Name'}),

                        ], [
                            'tenant_id' => $tenant->id,
                            'legacy_id' => $legacy_data->{'Staff Key'},
                            'name'      => $legacy_data->{'Staff Name'},
                            'status'    => ($legacy_data->{'Staff Currently Working'} == 'Yes' ? 'Working' : 'NotWorking'),
                            'data'      => $employee_data

                        ]
                    );
                } elseif ($legacy_data->{'Staff Type'} == 'Contractor') {
                    (new Guest)->firstOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'slug'      => Str::kebab($legacy_data->{'Staff Name'}),


                        ], [
                            'tenant_id'   => $tenant->id,
                            'legacy_id'   => $legacy_data->{'Staff Key'},
                            'status'      => $legacy_data->{'Staff Currently Working'} == 'Yes',
                            'data'        => $employee_data,
                            'name'        => $legacy_data->{'Staff Name'},
                            'description' => 'Contractor'

                        ]
                    );
                }


            }

        }
        return 0;
    }
}
