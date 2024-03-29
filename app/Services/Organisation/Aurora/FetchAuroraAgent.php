<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 08:42:26 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Models\Assets\Country;
use App\Models\Assets\Language;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraAgent extends FetchAurora
{
    use WithAuroraImages;

    protected function parseModel(): void
    {
        $phone = $this->auroraModelData->{'Agent Main Plain Mobile'};
        if ($phone == '') {
            $phone = $this->auroraModelData->{'Agent Main Plain Telephone'};
        }

        $currency_id = $this->parseCurrencyID($this->auroraModelData->{'Agent Default Currency Code'});
        $country_id  = $this->parseCountryID($this->auroraModelData->{'Agent Products Origin Country Code'});
        $country     = Country::find($country_id);

        $timezone_id = $country->timezones()->first()->id;
        $language_id = Language::where('code', 'en-gb')->first()->id;

        $code=strtolower($this->auroraModelData->{'Agent Code'});

        $code=preg_replace('/\s/', '', $code);
        $code=preg_replace('/^aw/', '', $code);

        $this->parsedData['agent'] =
            [
                'code'        => $code,
                'name'        => $this->auroraModelData->{'Agent Name'},
                'country_id'  => $country_id,
                'timezone_id' => $timezone_id,
                'language_id' => $language_id,
                'currency_id' => $currency_id,
                'email'       => $this->auroraModelData->{'Agent Main Plain Email'},
                'phone'       => $phone,
                'source_id'   => $this->organisation->id.':'.$this->auroraModelData->{'Agent Key'},
                'source_slug' => Str::kebab(strtolower($this->auroraModelData->{'Agent Code'})),
                'created_at'  => $this->auroraModelData->{'Agent Valid From'},
                'address'     => $this->parseAddress(prefix: 'Agent Contact', auAddressData: $this->auroraModelData)


            ];

        //    $this->parsePhoto();
    }

    private function parsePhoto(): void
    {
        $profile_images            = $this->getModelImagesCollection(
            'Agent',
            $this->auroraModelData->{'Agent Key'}
        )->map(function ($auroraImage) {
            return $this->fetchImage($auroraImage);
        });
        $this->parsedData['photo'] = $profile_images->toArray();
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Agent Dimension')
            ->where('Agent Key', $id)->first();
    }
}
