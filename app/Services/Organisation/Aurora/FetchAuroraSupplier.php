<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:29:05 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraSupplier extends FetchAurora
{
    use WithAuroraImages;

    protected function parseModel(): void
    {
        $agentData = Db::connection('aurora')->table('Agent Supplier Bridge')
            ->leftJoin('Agent Dimension', 'Agent Supplier Agent Key', '=', 'Agent Key')
            ->select('Agent Code')
            ->where('Agent Supplier Supplier Key', $this->auroraModelData->{'Supplier Key'})->first();


        $agent = null;

        if ($agentData) {
            $agent = $this->parseAgent(Str::kebab(strtolower($agentData->{'Agent Code'})));
            if (!$agent) {
                print "agent not found ".$agentData->{'Agent Supplier Agent Key'}." \n";

                return;
            }
        }

        if ($agent) {
            $this->parsedData['parent'] = $agent;
        } else {
            $this->parsedData['parent'] = $this->organisation->group;
        }

        $deletedAt = $this->parseDate($this->auroraModelData->{'Supplier Valid To'});
        if ($this->auroraModelData->{'Supplier Type'} != 'Archived') {
            $deletedAt = null;
        }
        $phone = $this->auroraModelData->{'Supplier Main Plain Mobile'};
        if ($phone == '') {
            $phone = $this->auroraModelData->{'Supplier Main Plain Telephone'};
        }


        $name = $this->auroraModelData->{'Supplier Nickname'};
        if (!$name) {
            $name = $this->auroraModelData->{'Supplier Name'};
        }

        $sourceSlug = Str::kebab(strtolower($this->auroraModelData->{'Supplier Code'}));
        if ($deletedAt) {
            $sourceSlug .= '-deleted';
        }

        $this->parsedData['supplier'] =
            [
                'name'         => $name,
                'code'         => preg_replace('/\s/', '-', $this->auroraModelData->{'Supplier Code'}),
                'company_name' => $this->auroraModelData->{'Supplier Company Name'},
                'contact_name' => $this->auroraModelData->{'Supplier Main Contact Name'},
                'email'        => $this->auroraModelData->{'Supplier Main Plain Email'},
                'phone'        => $phone,
                'currency_id'  => $this->parseCurrencyID($this->auroraModelData->{'Supplier Default Currency Code'}),
                'source_id'    => $this->organisation->id.':'.$this->auroraModelData->{'Supplier Key'},
                'source_slug'  => $sourceSlug,
                'created_at'   => $this->parseDate($this->auroraModelData->{'Supplier Valid From'}),
                'deleted_at'   => $deletedAt,
                'address'      => $this->parseAddress(prefix: 'Supplier Contact', auAddressData: $this->auroraModelData)

            ];

        $this->parsePhoto();
    }

    private function parsePhoto(): void
    {
        $profile_images            = $this->getModelImagesCollection(
            'Supplier',
            $this->auroraModelData->{'Supplier Key'}
        )->map(function ($auroraImage) {
            return $this->fetchImage($auroraImage);
        });
        $this->parsedData['photo'] = $profile_images->toArray();
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Supplier Dimension')
            ->where('Supplier Key', $id)->first();
    }
}
