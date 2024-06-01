<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 18 Apr 2023 17:00:29 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\CurrencyExchange;

use App\Models\Helpers\Currency;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class GetCurrencyExchange
{
    use AsAction;

    public string $commandSignature = 'currency:exchange {base_currency_code} {target_currency_code}';

    public function handle(Currency $baseCurrency, Currency $targetCurrency): float|null
    {
        $key  = 'current-currency-exchange:'.$baseCurrency->code.'-'.$targetCurrency->code;

        $currencyExchange = (float)Cache::get($key);
        if (!$currencyExchange) {
            try {

                $exchangeData          = FetchCurrencyExchange::run($baseCurrency, $targetCurrency);
                $currencyExchange      = $exchangeData['exchange'] ?? null;

            } catch (Exception) {
                return null;
            }

            if ($currencyExchange) {
                Cache::add($key, $currencyExchange, now()->addMinutes(15));
            }
        }


        return $currencyExchange;
    }


    public function asCommand(Command $command): int
    {
        $baseCurrency   = Currency::where('code', $command->argument('base_currency_code'))->firstOrFail();
        $targetCurrency = Currency::where('code', $command->argument('target_currency_code'))->firstOrFail();


        $exchange = $this->handle($baseCurrency, $targetCurrency);

        $command->info("Current exchange {$baseCurrency->code}→$targetCurrency->code : $exchange");

        return 0;
    }
}
