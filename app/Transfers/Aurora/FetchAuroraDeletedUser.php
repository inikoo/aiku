<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 28 Sept 2024 10:42:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class FetchAuroraDeletedUser extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData['employee'] = null;
        if (!$this->auroraModelData->{'User Deleted Metadata'}) {
            $auroraDeletedData = new stdClass();
        } else {
            $auroraDeletedData = json_decode(gzuncompress($this->auroraModelData->{'User Deleted Metadata'}));
            $auroraDeletedData = $auroraDeletedData->data;
        }


        $data = [
            'deleted' => ['source' => 'aurora']
        ];


        $username = $auroraDeletedData->{'User Handle'}.'-deleted_aurora_'.$this->organisation->id.'_'.$this->auroraModelData->{'User Deleted Key'};


        $this->parsedData['user'] =
            [

                'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'User Deleted Key'},
                'username'        => Str::kebab(Str::lower($username)),
                'status'          => false,
                'pivot_status'    => false,
                'created_at'      => $auroraDeletedData->{'User Created'},
                'legacy_password' => Str::random(60),
                'language_id'     => $this->parseLanguageID($auroraDeletedData->{'User Preferred Locale'}),
                'reset_password'  => false,
                'data'            => $data,
                'deleted_at'      => $this->auroraModelData->{'User Deleted Date'}
            ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('User Deleted Dimension')
            ->where('User Deleted Key', $id)->first();
    }
}
