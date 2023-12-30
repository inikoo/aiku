<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 14:07:28 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Models\SysAdmin\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraGuest extends FetchAurora
{
    use WithAuroraImages;
    use WithAuroraParsers;

    public function fetch(int $id): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        $userData = DB::connection('aurora')
            ->table('User Dimension')
            ->whereIn('User Type', ['Contractor', 'Staff'])
            ->where('User Parent Key', $this->auroraModelData->{'Staff Key'})->first();

        if (!$userData) {
            return $this->parsedData;
        }

        $this->auroraModelData->userData = $userData;


        $username = Str::snake($userData->{'User Handle'});
        if (User::where('username', $username)->exists()) {
            return $this->parsedData;
        }


        if ($this->auroraModelData) {
            $this->parseModel();
            $this->parsePhoto();
        }


        return $this->parsedData;
    }

    protected function parseModel(): void
    {
        $data = [];


        if ($this->auroraModelData->{'Staff Address'}) {
            $data['address'] = $this->auroraModelData->{'Staff Address'};
        }


        $this->parsedData['guest'] = [

            'alias'         => strtolower($this->auroraModelData->{'Staff Alias'}),
            'contact_name'  => $this->auroraModelData->{'Staff Name'},
            'date_of_birth' => $this->parseDate($this->auroraModelData->{'Staff Birthday'}),
            'created_at'    => $this->auroraModelData->{'Staff Valid From'},
            'data'          => $data,
            'source_id'     => $this->organisation->id.':'.$this->auroraModelData->{'Staff Key'},
            'username'      => Str::snake(str_replace($this->auroraModelData->userData->{'User Handle'}, ' ', '')),
            'password'      => app()->environment('local') ? 'hello' : Str::random(),

        ];

        if ($this->auroraModelData->{'Staff Email'}) {
            $this->parsedData['guest']['email'] = $this->auroraModelData->{'Staff Email'};
        }
        if ($this->auroraModelData->{'Staff Telephone'}) {
            $this->parsedData['guest']['phone'] = $this->auroraModelData->{'Staff Telephone'};
        }
        if ($this->auroraModelData->{'Staff Official ID'}) {
            $this->parsedData['guest']['identity_document_number'] = $this->auroraModelData->{'Staff Official ID'};
        }

        $this->parsedData['user'] = [
            'source_id' => $this->organisation->id.':'.$this->auroraModelData->userData->{'User Key'}
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


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Staff Dimension')
            ->where('Staff Key', $id)->first();
    }
}
