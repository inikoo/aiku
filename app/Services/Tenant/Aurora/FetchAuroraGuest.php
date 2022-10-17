<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 14:07:28 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraGuest extends FetchAurora
{
    use WithAuroraImages;
    use WithAuroraParsers;

    public function fetch(int $id): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        if ($this->auroraModelData) {
            $this->parseModel();
            $this->parsePhoto();
        }


        return $this->parsedData;
    }

    protected function parseModel(): void
    {
        $data   = [];


        if ($this->auroraModelData->{'Staff Address'}) {
            $data['address'] = $this->auroraModelData->{'Staff Address'};
        }

        $this->parsedData['guest'] = [

            'code'                     => strtolower($this->auroraModelData->{'Staff Alias'}),
            'name'                     => $this->auroraModelData->{'Staff Name'},
            'email'                    => $this->auroraModelData->{'Staff Email'},
            'phone'                    => $this->auroraModelData->{'Staff Telephone'},
            'identity_document_number' => $this->auroraModelData->{'Staff Official ID'},
            'date_of_birth'            => $this->parseDate($this->auroraModelData->{'Staff Birthday'}),
            'created_at' => $this->auroraModelData->{'Staff Valid From'},
            'data'      => $data,
            'source_id' => $this->auroraModelData->{'Staff Key'},

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
