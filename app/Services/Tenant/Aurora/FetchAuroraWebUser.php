<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 04 Nov 2022 15:34:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use App\Actions\SourceFetch\Aurora\FetchCustomers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraWebUser extends FetchAurora
{

    protected function parseModel(): void
    {
        $data = [];

        $hasPassword = $this->isSha256($this->auroraModelData->{'Website User Password'});

        $password=wordwrap(Str::random(), 4, '-', true);

        if ($hasPassword) {
            $data              = [
                'au_auth'=>[
                    'password'=>$this->auroraModelData->{'Website User Password'},
                    'tmp_password'=>$password
                ]
            ];
            $web_login_version = 'au';
        } else {
            $web_login_version = 'current';
        }



        $this->parsedData['customer'] = FetchCustomers::run($this->tenantSource, $this->auroraModelData->{'Website User Customer Key'});
        $this->parsedData['webUser']  =
            [
                'status'            => $this->auroraModelData->{'Website User Active'} == 'Yes',
                'type'              => 'web',
                'web_login_version' => $web_login_version,

                'source_id' => $this->auroraModelData->{'Website User Key'},

                'password' => $password,
                'data'     => $data,
                'username' => $this->auroraModelData->{'Website User Handle'},
                'email'    => $this->auroraModelData->{'Website User Handle'},


                'created_at' => $this->parseDate($this->auroraModelData->{'Website User Created'})
            ];
    }

    protected function isSha256($hash): bool
    {
        if (preg_match("/^([a-f0-9]{64})$/", $hash) == 1) {
            return true;
        } else {
            return false;
        }
    }

    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Website User Dimension')
            ->where('Website User Key', $id)->first();
    }

}
