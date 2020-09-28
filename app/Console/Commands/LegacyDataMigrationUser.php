<?php
/*
* Author: Raul Perusquia (raul@inikoo.com)
*Created:  Tue Jul 28 2020 21:55:24 GMT+0800 (Malaysia Time) Tioman, Malaysia
*Copyright (c) 2020, AIku.io
*/
namespace App\Console\Commands;

use App\Models\HR\Employee;
use App\Models\System\Guest;
use App\Tenant;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class LegacyDataMigrationUser extends Command {

    use TenantAware;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'relocate:users {--tenant=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        $tenant = Tenant::current();

        $_table = '`User Dimension`';

        if (Arr::get($tenant->data, 'legacy')) {
            print ('The tenant is '.$tenant->subdomain."\n");
            $this->set_legacy_connection($tenant->data['legacy']['db']);

            foreach (DB::connection('legacy')->select("select * from".' '.$_table, []) as $legacy_data) {
                if ($legacy_data->{'User Type'} == 'xStaff' or $legacy_data->{'User Type'} == 'Contractor') {

                    $user_parent_key = null;
                    switch ($legacy_data->{'User Type'}) {
                        case 'Staff':
                            $user_parent = 'employee';
                            if ($employee = (new Employee)->where('legacy_id', $legacy_data->{'User Parent Key'})->first()) {
                                $user_parent_key = $employee->id;
                            }
                            break;
                        case 'Contractor':
                            $user_parent = 'guest';
                            if ($guest = (new Guest)->where('legacy_id', $legacy_data->{'User Parent Key'})->first()) {
                                $user_parent_key = $guest->id;
                            }
                            break;

                        default:
                            $user_parent = $legacy_data->{'User Type'};
                    }

                    $user_settings = [];

                    $user_data = $this->fill_data(
                        [
                            'pwd_legacy' => 'User Password'
                        ], $legacy_data
                    );





                    (new User)->firstOrCreate(
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
                            'data'          => $user_data

                        ]
                    );
                }
            }

        }


        return 0;


    }

    public function set_legacy_connection($database_name) {


        $database_settings = data_get(config('database.connections'), 'mysql');

        data_set($database_settings, 'database', $database_name);
        config(['database.connections.legacy' => $database_settings]);
        DB::connection('legacy');

    }


    public function fill_data($fields, $legacy_data) {

        $data = [];
        foreach ($fields as $key => $legacy_key) {


            if (!empty($legacy_data->{$legacy_key})) {

                $key_path = preg_split('/\./', $key);
                if (count($key_path) == 1) {
                    $data[$key] = $legacy_data->{$legacy_key};
                } elseif (count($key_path) == 2) {
                    $data[$key_path[0]][$key_path[1]] = $legacy_data->{$legacy_key};
                } elseif (count($key_path) == 3) {
                    $data[$key_path[0]][$key_path[1]][$key_path[2]] = $legacy_data->{$legacy_key};
                }


            }
        }

        return $data;
    }
}
