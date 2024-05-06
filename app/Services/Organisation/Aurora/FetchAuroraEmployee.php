<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 18:40:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\HumanResources\JobPosition\JobPositionScopeEnum;
use App\Models\HumanResources\JobPosition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraEmployee extends FetchAurora
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
            $this->parsePositions();
        }


        return $this->parsedData;
    }

    protected function parsePositions(): void
    {
        $rawJobPositions = $this->parseJobPositions();

        $shops=[];
        if (Arr::has($this->parsedData, 'user')) {
            $userSourceData = explode(':', $this->parsedData['user']['source_id']);
            $shops          = $this->getAuroraUserShopScopes($userSourceData[1]);
        }

        $positions=[];
        foreach ($rawJobPositions as $jobPositionSlug) {
            $jobPosition = JobPosition::where('slug', $jobPositionSlug)->firstOrFail();
            $scopes      =[];
            if($jobPosition->scope==JobPositionScopeEnum::SHOPS) {
                $scopes=$shops;
            }if($jobPosition->scope==JobPositionScopeEnum::WAREHOUSES) {
                $scopes=$this->organisation->warehouses()->pluck('id')->all();
            }

            $positions[]=[
                'slug'  => $jobPosition->slug,
                'scopes'=> $scopes
            ];


        }


        $this->parsedData['employee']['positions'] = $positions;
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

            if ($auroraUserData->aiku_alt_username) {
                $username = $auroraUserData->aiku_alt_username;
            } else {
                $username = $auroraUserData->{'User Handle'};
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

    private function parseJobPositions(): array
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
                $jobPositionIds[$jobPositions[$jobPositionCode]] = $jobPositionCode;
            }
        }


        return $jobPositionIds;
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Staff Dimension')
            ->selectRaw('*,(select GROUP_CONCAT(`Role Code`) from `Staff Role Bridge` SRB where (SRB.`Staff Key`=`Staff Dimension`.`Staff Key`) ) as staff_positions')
            ->where('Staff Key', $id)->first();
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

    protected function getAuroraUserShopScopes($userID): array
    {
        $shops = [];


        foreach (
            DB::connection('aurora')
                ->table('User Right Scope Bridge')
                ->where('User Key', $userID)->get() as $rawScope
        ) {
            if ($rawScope->{'Scope'} == 'Store') {
                $shop             = $this->parseShop($this->organisation->id.':'.$rawScope->{'Scope Key'});
                $shops[$shop->id] = $shop->id;
            }
            if ($rawScope->{'Scope'} == 'Website') {
                $website = $this->parseWebsite($this->organisation->id.':'.$rawScope->{'Scope Key'});

                $shops[$website->shop_id] = $website->shop_id;
            }
        }


        return array_keys($shops);
    }

}
