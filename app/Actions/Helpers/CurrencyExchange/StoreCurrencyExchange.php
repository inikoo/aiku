<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:33 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\CurrencyExchange;

use App\Models\Helpers\Currency;
use App\Models\Helpers\CurrencyExchange;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCurrencyExchange
{
    use AsAction;

    public function handle(Currency $currency, $modelData): CurrencyExchange
    {
        /** @var CurrencyExchange $currencyExchange */
        $currencyExchange= $currency->exchanges()->create($modelData);
        return  $currencyExchange;
    }
}
