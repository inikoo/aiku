<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 29 Sept 2024 22:17:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraUser extends FetchAurora
{
    use WithAuroraImages;

    protected function parseModel(): void
    {
        if (!in_array($this->auroraModelData->{'User Type'}, ['Staff', 'Contractor'])) {
            return;
        }

        $parent                          = null;
        $this->parsedData['parent_type'] = 'Guest';
        if ($this->auroraModelData->{'User Type'} == 'Staff') {
            $this->parsedData['parent_type'] = 'Staff';

            $parent = $this->parseEmployee($this->organisation->id.':'.$this->auroraModelData->{'User Parent Key'});
        }

        $legacyPassword = $this->auroraModelData->{'User Password'};
        if (app()->isLocal()) {
            $legacyPassword = hash('sha256', 'hello');
        }

        if ($this->auroraModelData->aiku_alt_username) {
            $username = $this->auroraModelData->aiku_alt_username;
        } else {
            $username = $this->auroraModelData->{'User Handle'};
        }


        $status = $this->auroraModelData->{'User Active'} == 'Yes';

        $employeeState = Arr::get($this->parsedData, 'employee.state');
        if ($employeeState == EmployeeStateEnum::LEFT) {
            $status = false;
        }

        $this->parsedData['parent'] = $parent;



        $this->parsedData['user'] =
            [

                'source_id'         => $this->organisation->id.':'.$this->auroraModelData->{'User Key'},
                'username'          => Str::kebab(Str::lower($username)),
                'status'            => true,
                'user_model_status' => $status,
                'created_at'        => $this->auroraModelData->{'User Created'},
                'legacy_password'   => $legacyPassword,
                'language_id'       => $this->parseLanguageID($this->auroraModelData->{'User Preferred Locale'}),
                'reset_password'    => false,
                'fetched_at'        => now(),
                'last_fetched_at'   => now(),
                'positions'         => $this->parsePositions($this->auroraModelData->{'User Key'})
            ];

        $this->parseUserPhoto();
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('User Dimension')
            ->leftJoin('Staff Dimension','Staff Key','User Parent Key')
            ->selectRaw('*,(select GROUP_CONCAT(`Role Code`) from `Staff Role Bridge` SRB where (SRB.`Staff Key`=`Staff Dimension`.`Staff Key`) ) as staff_positions')
            ->selectRaw('(select GROUP_CONCAT(`User Group Key`) from `User Group User Bridge` UGUB where (UGUB.`User Key`=`User Dimension`.`User Key`) ) as staff_groups')

            ->where('User Key', $id)->first();
    }
}
