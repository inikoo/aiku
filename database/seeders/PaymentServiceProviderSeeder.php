<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 15 Sep 2023 20:56:02 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Seeders;

use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Assets\Country;
use App\Models\SysAdmin\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class PaymentServiceProviderSeeder extends Seeder
{
    public function run(): void
    {
        $paymentServiceProvidersData = collect(
            json_decode(
                Storage::disk('datasets')->get('payment-service-providers.json'),
                true
            )
        );
        $organisations = Group::first()->organisations;

        foreach ($organisations as $organisation) {
            $paymentServiceProvidersData->each(function ($modelData) use ($organisation) {
                $countries = collect(Arr::get($modelData, 'countries'));


                $paymentServiceProvider=PaymentServiceProvider::where('code', Arr::get($modelData, 'code'))->first();

                if(!$paymentServiceProvider) {
                    $paymentServiceProvider=StorePaymentServiceProvider::run($organisation, Arr::except($modelData, ['countries']));
                }

                $countryIds=$countries->map(
                    function ($countryCode) {
                        $country=Country::where('code', $countryCode)->first();
                        return $country->id;
                    }
                )->all();

                // $paymentServiceProvider->countries()->sync($countryIds);

                print "\033[32mSeeded\033[0m: $paymentServiceProvider->code has been seeded \n";
            });
        }
    }
}
