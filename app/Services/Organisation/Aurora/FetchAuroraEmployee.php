<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 18:40:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use App\Models\HumanResources\JobPosition;
use App\Models\Organisations\Organisation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraEmployee
{
    use WithAuroraImages;
    use WithAuroraParsers;


    private Organisation $organisation;
    private array $parsedData;
    private object $auroraModelData;

    function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
        $this->parsedData   = [];
    }

    public function fetch(int $id): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        if ($this->auroraModelData) {
            $this->parseModel();
            $this->parsePhoto();
            $this->parseJobPositions();
        }


        return $this->parsedData;
    }

    private function parseModel(): void
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

        $this->parsedData['employee'] = [

            'name'                     => $this->auroraModelData->{'Staff Name'},
            'email'                    => $this->auroraModelData->{'Staff Email'},
            'phone'                    => $this->auroraModelData->{'Staff Telephone'},
            'identity_document_number' => $this->auroraModelData->{'Staff Official ID'},
            'date_of_birth'            => $this->parseDate($this->auroraModelData->{'Staff Birthday'}),
            'worker_number'            => $this->auroraModelData->{'Staff ID'},
            'nickname'                 => strtolower($this->auroraModelData->{'Staff Alias'}),
            'employment_start_at'      => $this->parseDate($this->auroraModelData->{'Staff Valid From'}),
            'created_at'               => $this->auroraModelData->{'Staff Valid From'},
            'emergency_contact'        => $this->auroraModelData->{'Staff Next of Kind'},
            'job_title'                => $this->auroraModelData->{'Staff Job Title'},
            'salary'                   => $salary,
            'working_hours'            => $workingHours,

            'employment_end_at'      => $this->parseDate($this->auroraModelData->{'Staff Valid To'}),
            'type'                   => Str::snake($this->auroraModelData->{'Staff Type'}, '-'),
            'state'                  => match ($this->auroraModelData->{'Staff Currently Working'}) {
                'No' => 'left',
                default => 'working'
            },
            'data'                   => $data,
            'errors'                 => $errors,
            'organisation_source_id' => $this->auroraModelData->{'Staff Key'},

        ];
    }

    private function parsePhoto(): void
    {
        $profile_images = $this->getModelImagesCollection(
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
                        sourceCode:   $sourceStaffPosition
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


    private function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Staff Dimension')
            ->selectRaw('*,(select GROUP_CONCAT(`Role Code`) from `Staff Role Bridge` SRB where (SRB.`Staff Key`=`Staff Dimension`.`Staff Key`) ) as staff_positions')
            ->where('Staff Key', $id)->first();
    }

    protected function parseJobPosition($isSupervisor, $sourceCode): string
    {
        return match ($sourceCode) {
            'WAHM' => 'wah-m',
            'WAHSK' => 'wah-sk',
            'WAHSC' => 'wah-sc',
            'PICK' => 'dist-pik,dist-pak',
            'OHADM' => 'dist-m',
            'PRODM' => 'prod-m',
            'PRODO' => 'prod-w',
            'CUSM' => 'cus-m',
            'CUS' => 'cus-c',
            'MRK' => $isSupervisor ? 'mrk-m' : 'mrk-c',
            'WEB' => $isSupervisor ? 'web-m' : 'web-c',
            'HR' => $isSupervisor ? 'hr-m' : 'hr-c',
            default => strtolower($sourceCode)
        };
    }

}
