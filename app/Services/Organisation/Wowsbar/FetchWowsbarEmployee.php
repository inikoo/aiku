<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 13 Apr 2024 09:53:02 Central Indonesia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Wowsbar;

use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\HumanResources\JobPosition;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchWowsbarEmployee extends FetchWowsbar
{
    public function fetch(int $id): ?array
    {
        $this->wowModelData = $this->fetchData($id);

        if ($this->wowModelData) {
            $this->parseModel();
            //   $this->parseUser();
            //  $this->parsePhoto();
            //  $this->parseJobPositions();
        }


        return $this->parsedData;
    }

    protected function parseModel(): void
    {


        $this->parsedData['employee'] = [
            'alias'                    => $this->wowModelData->alias,
            'contact_name'             => $this->wowModelData->contact_name,
            'worker_number'            => $this->wowModelData->worker_number,
            'employment_start_at'      => $this->wowModelData->employment_start_at,
            'job_title'                => $this->wowModelData->job_title,
            'type'                     => $this->wowModelData->type,
            'state'                    => $this->wowModelData->state,
            'created_at'               => $this->wowModelData->created_at,
            'source_id'                => $this->organisation->id.':'.$this->wowModelData->id

        ];
    }

    private function parseUser(): void
    {
        $auroraUserData = DB::connection('aurora')
            ->table('User Dimension as users')
            ->leftJoin('Staff Dimension', 'Staff Key', 'User Parent Key')
            ->selectRaw('*,(select GROUP_CONCAT(`Role Code`) from `Staff Role Bridge` SRB where (SRB.`Staff Key`=`Staff Dimension`.`Staff Key`) ) as staff_positions')
            ->whereIn('User Type', ['Staff', 'Contractor'])
            ->where('users.aiku_ignore', 'No')
            ->where('User Parent Key', $this->wowModelData->{'Staff Key'})->first();

        if ($auroraUserData) {
            $legacyPassword = $auroraUserData->{'User Password'};
            if (app()->isLocal()) {
                $legacyPassword = hash('sha256', 'hello');
            }

            if($auroraUserData->aiku_alt_username) {
                $username=$auroraUserData->aiku_alt_username;
            } else {
                $username=$auroraUserData->{'User Handle'};
            }



            $this->parsedData['user'] = [
                'source_id'       => $this->organisation->id.':'.$auroraUserData->{'User Key'},
                'username'        => Str::kebab(Str::lower($username)),
                'status'          => $auroraUserData->{'User Active'} == 'Yes',
                'created_at'      => $auroraUserData->{'User Created'},
                'legacy_password' => $legacyPassword,
                'language_id'     => $this->parseLanguageID($auroraUserData->{'User Preferred Locale'}),
            ];
        }
    }

    // todo re do this function using Roles from the enum
    private function parseUserRoles(): void
    {
        $roles = RolesEnum::cases();


        foreach (DB::connection('aurora')->table('User Group User Bridge')->where('User Key', $this->wowModelData->{'User Key'})->select('User Group Key')->get() as $auRole) {
            $role = match ($auRole->{'User Group Key'}) {
                1, 15 => 'system-admin',
                6  => 'human-resources-clerk',
                20 => 'human-resources-manager',
                8  => 'procurement-clerk',
                21, 28 => 'procurement-manager',
                4 => 'production-operative',
                27, 7 => 'production-manager',
                3  => 'distribution-clerk',
                22 => 'distribution-manager',

                23 => 'accountant-manager',

                17 => 'distribution-dispatcher-manager',
                24 => 'distribution-dispatcher-picker',
                25 => 'distribution-dispatcher-packer',


                16 => 'customer-services-manager',
                2  => 'customer-services-clerk',
                18 => 'shop-manager',
                9  => 'shop-clerk',

                14, 5 => 'reports-analyst',
                29 => 'marketing-broadcaster-clerk',
                30 => 'marketing-broadcaster-manager',
                32 => 'fulfilment-manager',
                31 => 'fulfilment-clerk',

                default => $auRole->{'User Group Key'},
            };

            if (Arr::has($roles, $role)) {
                $roles[$role] = true;
            }
            //else{
            //    print "$role\n";
            //}
        }


        $this->parsedData['roles'] = array_keys(
            collect($roles)->filter(function ($value) {
                return $value;
            })->all()
        );
    }



    private function parsePhoto(): void
    {
        $profile_images            = $this->getModelImagesCollection(
            'Staff',
            $this->wowModelData->{'Staff Key'}
        )->map(function ($auroraImage) {
            return $this->fetchImage($auroraImage);
        });
        $this->parsedData['photo'] = $profile_images->toArray();
    }

    private function parseJobPositions(): void
    {
        $jobPositions = JobPosition::all()->pluck('id', 'slug')->all();

        $query= DB::connection('wowsbar')
            ->table('job_positionables')
            ->leftJoin('job_positions', 'job_positionables.job_position_id', '=', 'job_positions.id')
            ->where('job_positionables.job_positionable_type', 'Employee')
            ->where('job_positionables.job_positionable_id', $this->wowModelData->id)
            ->get();


        foreach($query as $jobPosition) {
            $this->parsedData['employee']['positions'][] =$this->parseJobPosition($jobPosition->slug);
        }



    }


    protected function fetchData($id): object|null
    {
        return DB::connection('wowsbar')
            ->table('employees')
         //   ->selectRaw('*,(select GROUP_CONCAT(`Role Code`) from `Staff Role Bridge` SRB where (SRB.`Staff Key`=`Staff Dimension`.`Staff Key`) ) as staff_positions')
            ->where('id', $id)->first();
    }

    protected function parseJobPosition($sourceCode): string
    {
        return match ($sourceCode) {
            'dev-m' => 'saas-m',
            'dev-w' => 'saas-c',
            default => $sourceCode
        };
    }
}
