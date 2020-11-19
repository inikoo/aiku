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
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class RelocateEmployees extends Command {

    use TenantAware, LegacyDataMigration;

    protected $signature = 'relocate:employees {--tenant=*}';
    protected $description = 'Relocate legacy employees';


    public function handle() {
        $this->tenant = Tenant::current();

        $_table = '`Staff Dimension`';

        if (Arr::get($this->tenant->data, 'legacy')) {
            print ('Relocation staff from '.$this->tenant->slug." ".$this->tenant->data['legacy']['db']."  \n");

            $this->set_legacy_connection($this->tenant->data['legacy']['db']);


            $count_data = DB::connection('legacy')->select("select count(*) as num from".' '.$_table, [])[0];

            $bar = $this->output->createProgressBar($count_data->num);
            $bar->setFormat('debug');

            foreach (DB::connection('legacy')->select("select * from".' '.$_table, []) as $legacy_data) {


                $this->relocate_employee($legacy_data);

                $bar->advance();
            }
            $bar->finish();
            print "\n";
        }

        return 0;
    }

    function relocate_employee($legacy_data) {

        $employee_data = fill_legacy_data(
            [
                'personal_identification' => 'Staff Official ID',
                'hr_identification'       => 'Staff ID',
                'next_of_kind.name'       => 'Staff Next of Kind',
                'date_of_birth'           => 'Staff Birthday',
                'email'                   => 'Staff Email',
                'phone'                   => 'Staff Telephone'


            ], $legacy_data
        );


        $imagesModelData = get_images_data(
            $this->tenant,
            [
                'object'     => 'Staff',
                'object_key' => $legacy_data->{'Staff Key'},
                'limit'      => 1

            ]
        );

        $attachmentModelData = $this->get_attachments_data(
            [
                'object'     => 'Staff',
                'object_key' => $legacy_data->{'Staff Key'}

            ]
        );


        if ($legacy_data->{'Staff Type'} != 'Contractor') {

            $type = 'permanent';
            if ($legacy_data->{'Staff Type'} == 'TemporalWorker') {
                $type = 'temporal';

            } elseif ($legacy_data->{'Staff Type'} == 'WorkExperience') {
                $type = 'workExperience';

            }

            $employee = (new Employee)->updateOrCreate(
                [
                    'legacy_id' => $legacy_data->{'Staff Key'},

                ], [
                    'tenant_id' => $this->tenant->id,
                    'type'      => $type,
                    'slug'      => Str::kebab($legacy_data->{'Staff Name'}),
                    'name'      => $legacy_data->{'Staff Name'},
                    'status'    => ($legacy_data->{'Staff Currently Working'} == 'Yes' ? 'working' : 'notWorking'),
                    'data'      => $employee_data,

                ]
            );


            sync_image(
                $employee, $imagesModelData, function ($_scope) {
                switch ($_scope) {
                    default:
                        return 'profile';
                }
            }
            );

            $this->sync_attachments(
                $employee, $attachmentModelData, function ($_scope) {
                switch ($_scope) {
                    default:
                        return strtolower($_scope);
                }
            }
            );


            $_table   = '`User Dimension`';
            $_where_1 = '`User Type`';
            $_where_2 = '`User Parent Key`';

            $legacy_user_data = DB::connection('legacy')->select(
                "select * from  $_table where  $_where_1=? and $_where_2=?", [
                                                                               'Staff',
                                                                               $legacy_data->{'Staff Key'}
                                                                           ]
            );
            if ($legacy_user_data) {

                $user              = $this->relocate_user('Employee', $employee, $legacy_user_data[0]);
                $employee->user_id = $user->id;
                $employee->save();


            }


        } else {
            $guest = (new Guest)->updateOrCreate(
                [
                    'legacy_id' => $legacy_data->{'Staff Key'},


                ], [
                    'tenant_id' => $this->tenant->id,
                    'slug'      => Str::kebab($legacy_data->{'Staff Name'}),

                    'status'      => ($legacy_data->{'Staff Currently Working'} == 'Yes' ? 'active' : 'inactive'),
                    'data'        => $employee_data,
                    'name'        => $legacy_data->{'Staff Name'},
                    'description' => 'Contractor'

                ]
            );

            sync_image(
                $guest, $imagesModelData, function ($_scope = '') {
                switch ($_scope) {
                    default:
                        return 'profile';
                }
            }
            );


            $_table   = '`User Dimension`';
            $_where_1 = '`User Type`';
            $_where_2 = '`User Parent Key`';

            $legacy_user_data = DB::connection('legacy')->select(
                "select * from  $_table where  $_where_1=? and $_where_2=?", [
                                                                               'Contractor',
                                                                               $legacy_data->{'Staff Key'}
                                                                           ]
            );

            if ($legacy_user_data) {

                $user = $this->relocate_user('Employee', $guest, $legacy_user_data[0]);

                $guest->user_id = $user->id;
                $guest->save();
            }


        }
    }

    function relocate_user($parent_type, $parent, $legacy_user_data) {


        $user_settings = [];

        $confidential_user_data = fill_legacy_data(
            [
                'pwd_legacy' => 'User Password'
            ], $legacy_user_data
        );
        $user_data              = fill_legacy_data(
            [
                'email'  => 'User Password Recovery Email',
                'mobile' => 'User Password Recovery Mobile'
            ], $legacy_user_data
        );

        return (new User)->updateOrCreate(
            [
                'tenant_id' => $this->tenant->id,
                'handle'    => Str::lower($legacy_user_data->{'User Handle'}),
            ], [
                'password'      => bcrypt($legacy_user_data->{'User Password'}),
                'pin'           => (config('app.env') == 'devel' ? Hash::make('1234') : Hash::make(Str::random(6))),
                'legacy_id'     => $legacy_user_data->{'User Key'},
                'userable_type' => $parent_type,
                'userable_id'   => $parent->id,
                'status'        => ($legacy_user_data->{'User Active'} == 'Yes' ? 'active' : 'suspended'),
                'settings'      => $user_settings,
                'confidential'  => $confidential_user_data,
                'data'          => $user_data

            ]
        );

    }

}


