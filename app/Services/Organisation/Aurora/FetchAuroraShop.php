<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:08:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraShop extends FetchAurora
{


    protected function parseModel(): void
    {
        $this->parsedData['shop'] = [

            'type'                     =>
                match ($this->auroraModelData->{'Store Type'}) {
                    'Dropshipping', 'Fulfilment' => 'fulfilment_house',
                    default => 'shop'
                },
            'subtype'                  => strtolower($this->auroraModelData->{'Store Type'}),
            'code'                     => $this->auroraModelData->{'Store Code'},
            'name'                     => $this->auroraModelData->{'Store Name'},
            'website'                  => $this->auroraModelData->{'Store URL'},
            'company_name'             => $this->auroraModelData->{'Store Company Name'},
            'contact_name'             => $this->auroraModelData->{'Store Contact Name'},
            'email'                    => $this->auroraModelData->{'Store Email'},
            'phone'                    => $this->auroraModelData->{'Store Telephone'},
            'tax_number'               => $this->auroraModelData->{'Store VAT Number'},
            'identity_document_number' => $this->auroraModelData->{'Store Company Number'},
            'tax_number_status'        => 'valid',


            'language_id' => $this->parseLanguageID($this->auroraModelData->{'Store Locale'}),
            'currency_id' => $this->parseCurrencyID($this->auroraModelData->{'Store Currency Code'}),
            'timezone_id' => $this->parseTimezoneID($this->auroraModelData->{'Store Timezone'}),
            'state'       => Str::snake($this->auroraModelData->{'Store Status'} == 'Normal' ? 'Open' : $this->auroraModelData->{'Store Status'}, '-'),
            'open_at'     => $this->parseDate($this->auroraModelData->{'Store Valid From'}),
            'closed_at'   => $this->parseDate($this->auroraModelData->{'Store Valid To'}),
            'created_at'  => $this->parseDate($this->auroraModelData->{'Store Valid From'}),

            'organisation_source_id' => $this->auroraModelData->{'Store Key'},

        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Store Dimension')
            ->where('Store Key', $id)->first();
    }

}
