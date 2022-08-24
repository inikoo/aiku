<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 24 Aug 2022 15:53:13 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Organisation\Aurora;

use App\Models\Organisations\Organisation;
use Illuminate\Support\Facades\DB;

class FetchAuroraUser
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
            $this->parseAvatar();
            $this->parseRoles();
        }


        return $this->parsedData;
    }

    private function parseModel(): void
    {
        $this->parsedData['user'] = [
            'status'      => $this->auroraModelData->{'User Active'} == 'Yes',
            'language_id' => $this->parseLanguageID($this->auroraModelData->{'User Preferred Locale'}),
        ];
    }

    private function parseAvatar(): void
    {
        $profile_images = collect();
        if ($this->auroraModelData->{'User Type'} == 'Staff') {
            $profile_images = $this->getModelImagesCollection(
                'Staff',
                $this->auroraModelData->{'User Parent Key'}
            )->map(function ($auroraImage) use ($profile_images) {
                return $this->fetchImage($auroraImage);
            });
        }
        $this->parsedData['profile_images'] = $profile_images->toArray();
    }

    private function parseRoles(): void
    {
        $roles = collect(config('blueprint.roles'))->mapWithKeys(function ($item, $key) {
            return [$key => false];
        })->all();

        if($this->auroraModelData->{'User Active'}=='Yes' and $this->auroraModelData->{'Staff Currently Working'}??false=='Yes'){

            $staffPositions=explode(',',$this->auroraModelData->staff_positions);
            if(in_array('PICK',$staffPositions)){
                $roles['picker']=true;
                $roles['packer']=true;
            }
            if(in_array('WAHM',$staffPositions)){
                $roles['supervisor']=true;
            }

            if(count($roles)==0){
                if(in_array('DIR',$staffPositions)){
                    $roles['observer']=true;
                }else{
                    $roles['guest']=true;
                }
            }

        }

        $this->parsedData['roles']=array_keys(collect($roles)->filter(function ($value) {
            return $value ;
        })->all());

    }


    private function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('User Dimension')
            ->leftJoin('Staff Dimension','Staff Key','User Parent Key')
            ->selectRaw('*,(select GROUP_CONCAT(`Role Code`) from `Staff Role Bridge` SRB where (SRB.`Staff Key`=`Staff Dimension`.`Staff Key`) ) as staff_positions')
            ->whereIn('User Type',['Staff','Contractor'])
            ->where('User Key', $id)->first();
    }

}
