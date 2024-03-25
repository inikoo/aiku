<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 26 May 2023 16:53:17 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Factories\Accounting;

use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Assets\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $currency = Currency::first();

        return [
            'number'      => '00001',
            'type'        => InvoiceTypeEnum::INVOICE,
            'currency_id' => $currency->id,
            'exchange'    => 1,
            'net'         => 10,
            'total'       => 10,
            'payment'     => 0,
            'data'        => [],
        ];
    }
}
