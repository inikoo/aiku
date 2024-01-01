<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:08:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Market\Shop\ShopTypeEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraShop extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData['source_department_key'] = $this->auroraModelData->{'Store Department Category Key'};
        $this->parsedData['source_family_key']     = $this->auroraModelData->{'Store Family Category Key'};

        if ($this->auroraModelData->{'Store Can Collect'} and $this->auroraModelData->{'Store Collect Address Country 2 Alpha Code'}) {
            $this->parsedData['collectionAddress'] = $this->parseAddress(prefix: 'Store Collect', auAddressData: $this->auroraModelData);
        }


        $auroraSettings = json_decode($this->auroraModelData->{'Store Settings'}, true);


        $this->parsedData['tax_number'] = $this->parseTaxNumber(
            number: $this->auroraModelData->{'Store VAT Number'},
            countryID: $this->parseCountryID($auroraSettings['tax_country_code'])
        );

        $this->parsedData['shop'] = [

            'type'         =>
                match ($this->auroraModelData->{'Store Type'}) {
                    'Dropshipping', 'Fulfilment' => ShopTypeEnum::FULFILMENT_HOUSE->value,
                    default => ShopTypeEnum::SHOP->value
                },
            'subtype'      => strtolower($this->auroraModelData->{'Store Type'}),
            'code'         => $this->auroraModelData->{'Store Code'},
            'name'         => $this->auroraModelData->{'Store Name'},
            'company_name' => $this->auroraModelData->{'Store Company Name'},
            'contact_name' => $this->auroraModelData->{'Store Contact Name'},
            'email'        => $this->auroraModelData->{'Store Email'},
            'phone'        => $this->auroraModelData->{'Store Telephone'},

            'identity_document_number' => $this->auroraModelData->{'Store Company Number'},

            'country_id'  => $this->parseCountryID($this->auroraModelData->{'Store Home Country Code 2 Alpha'}),
            'language_id' => $this->parseLanguageID($this->auroraModelData->{'Store Locale'}),
            'currency_id' => $this->parseCurrencyID($this->auroraModelData->{'Store Currency Code'}),
            'timezone_id' => $this->parseTimezoneID($this->auroraModelData->{'Store Timezone'}),
            'state'       => Str::snake($this->auroraModelData->{'Store Status'} == 'Normal' ? 'Open' : $this->auroraModelData->{'Store Status'}, '-'),
            'open_at'     => $this->parseDate($this->auroraModelData->{'Store Valid From'}),
            'closed_at'   => $this->parseDate($this->auroraModelData->{'Store Valid To'}),
            'created_at'  => $this->parseDate($this->auroraModelData->{'Store Valid From'}),

            'source_id' => $this->organisation->id.':'.$this->auroraModelData->{'Store Key'},
            'settings'  => [
                'can_collect' => $this->auroraModelData->{'Store Can Collect'} === 'Yes'
            ]

        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Store Dimension')
            ->where('Store Key', $id)->first();
    }
}
