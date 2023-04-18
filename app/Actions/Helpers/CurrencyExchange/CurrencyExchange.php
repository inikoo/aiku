<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 10:28:39 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\CurrencyExchange;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;
use AmrShawky\LaravelCurrency\Facade\Currency;

class CurrencyExchange
{
    use AsAction;

    public string $commandSignature = 'currency:exchange {from} {to}';

    public function handle($fromCurrency, $toCurrency, $date): float|null
    {
        $date = $date ?? now()->format('Y-m-d');
        $key = $fromCurrency . '-' . $toCurrency . '-' . $date;

        $currencyExchange = (float)Cache::get($key);

        if (!$currencyExchange) {
            $currencyExchange = Currency::convert()
                ->from($fromCurrency)
                ->to($toCurrency)
                ->date($date)
                ->get();
        }

        Cache::add($key, $currencyExchange, now()->addMinutes(15));

        return $currencyExchange;
    }


    public function asCommand(Command $command): int
    {
        $this->handle($command->argument('from'), $command->argument('to'), now());

        return 0;
    }
}
