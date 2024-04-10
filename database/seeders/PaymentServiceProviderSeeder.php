<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 15 Sep 2023 20:56:02 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Seeders;

use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderEnum;
use App\Models\Accounting\PaymentServiceProvider;
use Illuminate\Database\Seeder;

class PaymentServiceProviderSeeder extends Seeder
{
    public function run(): void
    {
        $paymentServiceProvidersData = collect(PaymentServiceProviderEnum::values());

        $paymentServiceProvidersData->each(function ($modelData) {
            $paymentServiceProvider=PaymentServiceProvider::where('code', $modelData)->first();

            if(!$paymentServiceProvider) {
                StorePaymentServiceProvider::run([
                    'code' => $modelData,
                    'type' => PaymentServiceProviderEnum::types()[$modelData],
                    'name' => PaymentServiceProviderEnum::labels()[$modelData]
                ]);
            }

        });
    }

}
