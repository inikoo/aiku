<?php
/*
* Author: Raul Perusquia (raul@inikoo.com)
*Created:  Tue Jul 28 2020 21:55:24 GMT+0800 (Malaysia Time) Tioman, Malaysia
*Copyright (c) 2020, AIku.io
*/

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;
use App\Models\HR\Employee;
use App\Models\System\Guest;
use App\Tenant;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateUsers extends Command {

    use TenantAware, LegacyDataMigration;


    protected $signature = 'relocate:users {--tenant=*}';
    protected $description = 'Relocate legacy users';

    public function __construct() {
        parent::__construct();
    }

    public function handle() {
        $tenant = Tenant::current();

        $_table = '`User Dimension`';

        if (Arr::get($tenant->data, 'legacy')) {
            print ('The tenant is '.$tenant->subdomain."\n");
            $this->set_legacy_connection($tenant->data['legacy']['db']);

            foreach (DB::connection('legacy')->select("select * from".' '.$_table, []) as $legacy_data) {
                if ($legacy_data->{'User Type'} == 'Staff' or $legacy_data->{'User Type'} == 'Contractor') {


                    $user_parent_key = null;
                    switch ($legacy_data->{'User Type'}) {
                        case 'Staff':
                            $user_parent = 'Employee';

                            if ($employee = (new Employee)->where('legacy_id', $legacy_data->{'User Parent Key'})->first()) {
                                $user_parent_key = $employee->id;
                            }
                            break;
                        case 'Contractor':
                            $user_parent = 'Guest';
                            if ($guest = (new Guest)->where('legacy_id', $legacy_data->{'User Parent Key'})->first()) {
                                $user_parent_key = $guest->id;
                            }
                            break;

                        default:
                            $user_parent = $legacy_data->{'User Type'};
                    }

                    $user_settings = [];

                    $confidential_user_data = $this->fill_data(
                        [
                            'pwd_legacy' => 'User Password'
                        ], $legacy_data
                    );
                    $user_data              = $this->fill_data(
                        [

                        ], $legacy_data
                    );

                    (new User)->updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'handle'    => Str::lower($legacy_data->{'User Handle'}),
                        ], [
                            'password'      => bcrypt($legacy_data->{'User Password'}),
                            'pin'           => (env('APP_ENV', 'production') == 'devel' ? Hash::make('1234') : Hash::make(Str::random(6))),
                            'legacy_id'     => $legacy_data->{'User Key'},
                            'userable_type' => $user_parent,
                            'userable_id'   => $user_parent_key,
                            'status'        => $legacy_data->{'User Active'} == 'Yes',
                            'settings'      => $user_settings,
                            'confidential'  => $confidential_user_data,
                            'data'          => $user_data

                        ]
                    );


                }
            }

        }


        return 0;


    }


}
