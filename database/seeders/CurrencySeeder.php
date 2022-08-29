<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 26 Aug 2021 05:39:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace Database\Seeders;

use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Intl\Currency\CurrencyRepository;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $currencyRepository = new CurrencyRepository();

        foreach( $currencyRepository->getAll() as $currency){
            Currency::UpdateOrCreate(
                ['code' => $currency->getCurrencyCode()],
                [
                    'name'           => $currency->getName(),
                    'symbol'         => $currency->getSymbol(),
                    'fraction_digits' => $currency->getFractionDigits(),


                ]
            );
        }

        $countryRepository = new CountryRepository();
        $countryList = $countryRepository->getList('en-GB');
        foreach ($countryList as $countryCode => $countryName) {
            if ($country = Country::where('code', $countryCode)->first()) {

                $_country = $countryRepository->get($countryCode);


                if($currency=Currency::where('code', $_country->getCurrencyCode())->first()){
                    $country->currency_id=$currency->id;
                    $country->save();
                }else{
                    print "Currency not found : {$_country->getCurrencyCode()} for country $countryCode\n";
                }

            }
        }



    }

}
