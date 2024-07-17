<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Jul 2024 14:16:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\TaxCategory;

use App\Enums\Helpers\TaxCategories\TaxCategoryTypeEnum;
use App\Models\Helpers\Address;
use App\Models\Helpers\Country;
use App\Models\Helpers\TaxCategory;
use App\Models\Helpers\TaxNumber;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTaxCategory
{
    use AsAction;

    public function handle(Country $country, ?TaxNumber $taxNumber, Address $billingAddress, ?Address $deliveryAddress): TaxCategory
    {
        return match ($country->code) {
            'GB'    => $this->gbTaxCategory($billingAddress, $deliveryAddress),
            default => TaxCategory::find(1)
        };
    }


    protected function gbTaxCategory(Address $billingAddress, ?Address $deliveryAddress): TaxCategory
    {

        $gbCountryId      =Country::where('code', 'GB')->first()->id;
        $taxableCountries = ['GB', 'IM'];

        if (in_array($billingAddress->country_code, $taxableCountries) or in_array($deliveryAddress->country_code, $taxableCountries)) {
            return TaxCategory::where('type', TaxCategoryTypeEnum::STANDARD)->where('country_id', $gbCountryId)->where('status', true)->first();
        }

        return  TaxCategory::find(1);

    }


}
