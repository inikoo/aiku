<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 13 Apr 2024 09:53:02 Central Indonesia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Wowsbar;

use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\HumanResources\JobPosition;
use App\Services\Organisation\Aurora\FetchAurora;
use App\Services\Organisation\Aurora\WithAuroraImages;
use App\Services\Organisation\Aurora\WithAuroraParsers;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchWowsbarEmployee extends FetchAurora
{
    use WithAuroraImages;
    use WithAuroraParsers;


    public function fetch(int $id): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        if ($this->auroraModelData) {
            $this->parseModel();
            $this->parseUser();
            $this->parsePhoto();
            $this->parseJobPositions();
        }


        return $this->parsedData;
    }

    protected function parseModel(): void
    {
        $data   = [];
        $errors = [];
        if ($this->parseDate($this->auroraModelData->{'Staff Valid From'}) == '') {
            $errors = [
                'missing' => ['created_at', 'employment_start_at']
            ];
        }


        $working_hours = json_decode($this->auroraModelData->{'Staff Working Hours'}, true);
        if ($working_hours) {
            $working_hours['week_distribution'] = array_change_key_case(
                json_decode($this->auroraModelData->{'Staff Working Hours Per Week Metadata'}, true)
            );
        }
        $workingHours     = json_decode($this->auroraModelData->{'Staff Working Hours'}, true);
        $weekDistribution = json_decode($this->auroraModelData->{'Staff Working Hours Per Week Metadata'}, true);

        if ($workingHours and $weekDistribution) {
            $workingHours['week_distribution'] = array_change_key_case($weekDistribution);
        }


        $salary = json_decode($this->auroraModelData->{'Staff Salary'}, true);
        if ($salary) {
            $salary = array_change_key_case($salary);
        }

        if ($this->auroraModelData->{'Staff Address'}) {
            $data['address'] = $this->auroraModelData->{'Staff Address'};
        }

        $this->parsedData['working_hours'] = $working_hours ?? [];


        $positions = [];


        if ($this->auroraModelData->{'Staff ID'}) {
            $workerNumber = preg_replace('/[()]/', '', $this->auroraModelData->{'Staff ID'});
            $workerNumber = preg_replace('/\s+/', '-', $workerNumber);
        } else {
            $workerNumber = $this->auroraModelData->{'Staff Key'};
        }

        $this->parsedData['employee'] = [
            'alias'                    => $this->auroraModelData->{'Staff Alias'},
            'contact_name'             => $this->auroraModelData->{'Staff Name'},
            'email'                    => $this->auroraModelData->{'Staff Email'} ?: null,
            'phone'                    => $this->auroraModelData->{'Staff Telephone'} ?: null,
            'identity_document_number' => $this->auroraModelData->{'Staff Official ID'} ?: null,
            'date_of_birth'            => $this->parseDate($this->auroraModelData->{'Staff Birthday'}),
            'worker_number'            => $workerNumber,
            'created_at'               => $this->auroraModelData->{'Staff Valid From'},
            'emergency_contact'        => $this->auroraModelData->{'Staff Next of Kind'} ?: null,
            'job_title'                => $this->auroraModelData->{'Staff Job Title'} ?: null,
            'salary'                   => $salary,
            'employment_start_at'      => $this->parseDate($this->auroraModelData->{'Staff Valid From'}),
            'employment_end_at'        => $this->parseDate($this->auroraModelData->{'Staff Valid To'}),
            'type'                     => Str::snake($this->auroraModelData->{'Staff Type'}, '-'),
            'state'                    => match ($this->auroraModelData->{'Staff Currently Working'}) {
                'No'    => 'left',
                default => 'working'
            },
            'data'                     => $data,
            'errors'                   => $errors,
            'source_id'                => $this->organisation->id.':'.$this->auroraModelData->{'Staff Key'},
            'positions'                => $positions,

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
            ->where('User Parent Key', $this->auroraModelData->{'Staff Key'})->first();

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


        foreach (DB::connection('aurora')->table('User Group User Bridge')->where('User Key', $this->auroraModelData->{'User Key'})->select('User Group Key')->get() as $auRole) {
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
            $this->auroraModelData->{'Staff Key'}
        )->map(function ($auroraImage) {
            return $this->fetchImage($auroraImage);
        });
        $this->parsedData['photo'] = $profile_images->toArray();
    }

    private function parseJobPositions(): void
    {
        $jobPositions = JobPosition::all()->pluck('id', 'slug')->all();

        $jobPositionCodes = [];
        foreach (explode(',', $this->auroraModelData->staff_positions) as $sourceStaffPosition) {
            $jobPositionCodes = array_merge(
                $jobPositionCodes,
                explode(
                    ',',
                    $this->parseJobPosition(
                        isSupervisor: $this->auroraModelData->{'Staff Is Supervisor'} == 'Yes',
                        sourceCode: $sourceStaffPosition
                    )
                )
            );
        }
        $jobPositionIds = [];

        foreach ($jobPositionCodes as $jobPositionCode) {
            if (array_key_exists($jobPositionCode, $jobPositions)) {
                $jobPositionIds[$jobPositions[$jobPositionCode]] = $jobPositions[$jobPositionCode];
            }
        }

        $this->parsedData['job-positions'] = $jobPositionIds;
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('wowsbar')
            ->table('employees')
         //   ->selectRaw('*,(select GROUP_CONCAT(`Role Code`) from `Staff Role Bridge` SRB where (SRB.`Staff Key`=`Staff Dimension`.`Staff Key`) ) as staff_positions')
            ->where('id', $id)->first();
    }

    protected function parseJobPosition($isSupervisor, $sourceCode): string
    {
        return match ($sourceCode) {
            'WAHM'  => 'wah-m',
            'WAHSK' => 'wah-sk',
            'WAHSC' => 'wah-sc',
            'PICK'  => 'dist-pik,dist-pak',
            'OHADM' => 'dist-m',
            'PRODM' => 'prod-m',
            'PRODO' => 'prod-w',
            'CUSM'  => 'cus-m',
            'CUS'   => 'cus-c',
            'MRK'   => $isSupervisor ? 'mrk-m' : 'mrk-c',
            'WEB'   => $isSupervisor ? 'web-m' : 'web-c',
            'HR'    => $isSupervisor ? 'hr-m' : 'hr-c',
            default => strtolower($sourceCode)
        };
    }
}
