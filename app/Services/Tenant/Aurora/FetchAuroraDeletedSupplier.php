<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Feb 2023 11:51:39 Malaysia Time, Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraDeletedSupplier extends FetchAurora
{
    protected function parseModel(): void
    {
        $auroraDeletedData = json_decode(gzuncompress($this->auroraModelData->{'Supplier Deleted Metadata'}));

        $agentData = Db::connection('aurora')->table('Agent Supplier Bridge')
            ->select('Agent Supplier Agent Key')
            ->where('Agent Supplier Supplier Key', $auroraDeletedData->{'Supplier Key'})->first();

        $agentId                   = null;
        $this->parsedData['owner'] = app('currentTenant');

        if ($agentData) {
            $agent = $this->parseAgent($agentData->{'Agent Supplier Agent Key'});
            if (!$agent) {
                print "agent not found ".$agentData->{'Agent Supplier Agent Key'}." \n";
                return;
            }
            $this->parsedData['agent']=$agent;
            $agentId                  = $agent->id;
        }

        $phone = $auroraDeletedData->{'Supplier Main Plain Mobile'} ?? null;
        if ($phone == '') {
            $phone = $auroraDeletedData->{'Supplier Main Plain Telephone'};
        }

        $deleted_at = $this->auroraModelData->{'Supplier Deleted Date'};

        $this->parsedData['supplier'] =
            [
                'agent_id'     => $agentId,
                'name'         => $auroraDeletedData->{'Supplier Nickname'} ?? $auroraDeletedData->{'Supplier Name'},
                'code'         => preg_replace('/\s/', '-', $auroraDeletedData->{'Supplier Code'}),
                'company_name' => $auroraDeletedData->{'Supplier Company Name'},
                'contact_name' => $auroraDeletedData->{'Supplier Main Contact Name'},
                'email'        => $auroraDeletedData->{'Supplier Main Plain Email'},
                'phone'        => $phone,
                'currency_id'  => $this->parseCurrencyID($auroraDeletedData->{'Supplier Default Currency Code'}),
                'source_id'    => $auroraDeletedData->{'Supplier Key'},
                'created_at'   => $auroraDeletedData->{'Supplier Valid From'} ?? null,
                'deleted_at'   => $deleted_at,

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
