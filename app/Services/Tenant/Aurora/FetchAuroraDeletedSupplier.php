<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Feb 2023 11:51:39 Malaysia Time, Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use App\Models\Procurement\AgentTenant;
use Illuminate\Support\Facades\DB;

class FetchAuroraDeletedSupplier extends FetchAurora
{
    protected function parseModel(): void
    {
        $auroraDeletedData = json_decode(gzuncompress($this->auroraModelData->{'Supplier Deleted Metadata'}));


        $phone = $auroraDeletedData->{'Supplier Main Plain Mobile'} ?? null;
        if ($phone == '') {
            $phone = $auroraDeletedData->{'Supplier Main Plain Telephone'};
        }

        $deleted_at = $this->auroraModelData->{'Supplier Deleted Date'};


        $type                      = 'supplier';
        $this->parsedData['owner'] = app('currentTenant');
        $agentId                   = null;


        $agentData = Db::connection('aurora')->table('Agent Supplier Bridge')
            ->select('Agent Supplier Agent Key')
            ->where('Agent Supplier Supplier Key', $auroraDeletedData->{'Supplier Key'})->first();


        if ($agentData) {

            $agentTenant=AgentTenant::where('source_id', $agentData->{'Agent Supplier Agent Key'})
                ->where('tenant_id', app('currentTenant')->id)->first();

            $this->parsedData['owner']=$agentTenant->agent;

            if (!$this->parsedData['owner']) {
                print "agent not found ".$agentData->{'Agent Supplier Agent Key'}." \n";
            }
            $agentId = $this->parsedData['owner']->id;
            $type    = 'sub-supplier';
        }

        $this->parsedData['supplier'] =
            [
                'type'     => $type,
                'agent_id' => $agentId,
                'name'     => $auroraDeletedData->{'Supplier Nickname'} ?? $auroraDeletedData->{'Supplier Name'},
                'code'     => preg_replace('/\s/', '-', $auroraDeletedData->{'Supplier Code'}),

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
