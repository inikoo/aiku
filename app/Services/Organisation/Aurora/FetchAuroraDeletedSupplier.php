<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Feb 2023 11:51:39 Malaysia Time, Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraDeletedSupplier extends FetchAurora
{
    protected function parseModel(): void
    {
        $auroraDeletedData = json_decode(gzuncompress($this->auroraModelData->{'Supplier Deleted Metadata'}));

        $agentData = Db::connection('aurora')->table('Agent Supplier Bridge')
            ->leftJoin('Agent Dimension', 'Agent Supplier Agent Key', '=', 'Agent Key')
            ->select('Agent Code')
            ->where('Agent Supplier Supplier Key', $auroraDeletedData->{'Supplier Key'})->first();

        $agent = null;

        if ($agentData) {
            $agent = $this->parseAgent(Str::kebab(strtolower($agentData->{'Agent Code'})));
            if (!$agent) {
                print "agent not found ".$agentData->{'Agent Code'}." \n";

                return;
            }
        }

        if ($agent) {
            $this->parsedData['parent'] = $agent;
        } else {
            $this->parsedData['parent'] = $this->organisation->group;
        }


        $phone = $auroraDeletedData->{'Supplier Main Plain Mobile'} ?? null;
        if ($phone == '') {
            $phone = $auroraDeletedData->{'Supplier Main Plain Telephone'};
        }

        $deleted_at = $this->auroraModelData->{'Supplier Deleted Date'};

        $this->parsedData['supplier'] =
            [
                'name'         => $auroraDeletedData->{'Supplier Nickname'} ?? $auroraDeletedData->{'Supplier Name'},
                'code'         => preg_replace('/\s/', '-', $auroraDeletedData->{'Supplier Code'}),
                'company_name' => $auroraDeletedData->{'Supplier Company Name'},
                'contact_name' => $auroraDeletedData->{'Supplier Main Contact Name'},
                'email'        => $auroraDeletedData->{'Supplier Main Plain Email'},
                'phone'        => $phone,
                'currency_id'  => $this->parseCurrencyID($auroraDeletedData->{'Supplier Default Currency Code'}),
                'source_id'    => $this->organisation->id.':'.$auroraDeletedData->{'Supplier Key'},
                'source_slug'  => Str::kebab(strtolower($auroraDeletedData->{'Supplier Code'}).'-deleted'),
                'created_at'   => $auroraDeletedData->{'Supplier Valid From'} ?? null,
                'deleted_at'   => $deleted_at,
                'address'      => $this->parseAddress(prefix: 'Supplier Contact', auAddressData: $auroraDeletedData)

            ];


        $this->parsedData['address'] = $this->parseAddress(prefix: 'Supplier Contact', auAddressData: $auroraDeletedData);
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Supplier Deleted Dimension')
            ->where('Supplier Deleted Key', $id)->first();
    }
}
