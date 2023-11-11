<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 04 Nov 2022 15:34:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Auth\WebUser\WebUserAuthTypeEnum;
use App\Enums\Auth\WebUser\WebUserTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraWebUser extends FetchAurora
{
    protected function parseModel(): void
    {
        $data = [];


        $hasPassword = $this->isSha256($this->auroraModelData->{'Website User Password'});

        if ($hasPassword) {
            $authType = WebUserAuthTypeEnum::AURORA;

            $legacyPassword = $this->auroraModelData->{'Website User Password'};
            if (app()->isLocal() || app()->environment('testing')) {
                $legacyPassword = hash('sha256', 'hello');
            }

            $data['legacy_password'] = $legacyPassword;
            $password                = null;
        } else {
            $authType = WebUserAuthTypeEnum::DEFAULT;
            $password = (app()->isLocal() || app()->environment('testing') ? 'hello' : wordwrap(\Illuminate\Support\Str::random(), 4, '-', true));
        }


        $this->parsedData['customer'] = $this->parseCustomer($this->auroraModelData->{'Website User Customer Key'});
        $this->parsedData['webUser']  =
            [
                'status'     => $this->auroraModelData->{'Website User Active'} == 'Yes',
                'type'       => WebUserTypeEnum::WEB,
                'auth_type'  => $authType,
                'source_id'  => $this->auroraModelData->{'Website User Key'},
                'data'       => $data,
                'username'   => $this->auroraModelData->{'Website User Handle'},
                'email'      => $this->auroraModelData->{'Website User Handle'},
                'created_at' => $this->parseDate($this->auroraModelData->{'Website User Created'})
            ];

        if($password) {
            data_set($this->parsedData, 'webUser.password', $password);
        }

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
