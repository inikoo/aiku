<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 10:28:39 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\CurrencyExchange;

use App\Actions\Helpers\CurrencyExchange\Providers\FetchCurrencyExchangeCurrencyBeacon;
use App\Actions\Helpers\CurrencyExchange\Providers\FetchCurrencyExchangeFrankfurter;
use App\Models\Helpers\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchCurrencyExchange
{
    use AsAction;

    public string $commandSignature = 'currency:historic-exchange {base_currency_code} {target_currency_code} {date} {--p|provider=}';

    public function handle(Currency $baseCurrency, Currency $targetCurrency, ?Carbon $date = null, string $provider = null): array
    {
        if ($baseCurrency->code == $targetCurrency) {
            return [
                'status'   => 'success',
                'exchange' => 1,
                'source'   => null
            ];
        }


        if (!$provider) {
            $providers = $this->getAvailableProviders();
        } else {
            $providers = Arr::only($this->getAvailableProviders(), $provider);
        }

        foreach ($providers as $provider) {
            $exchangeData = match ($provider) {
                'Frankfurter'    => FetchCurrencyExchangeFrankfurter::run($baseCurrency, $targetCurrency, $date),
                'CurrencyBeacon' => FetchCurrencyExchangeCurrencyBeacon::run($baseCurrency, $targetCurrency, $date),
                default          => [
                    'status'   => 'error',
                    'exchange' => null,
                    'source'   => null
                ]
            };

            if ($exchangeData['status'] == 'success') {
                return $exchangeData;
            }
        }


        return [
            'status'   => 'error',
            'exchange' => null,
            'source'   => null
        ];
    }


    private function getAvailableProviders(): array
    {
        return [
            'CB' => 'CurrencyBeacon',
            'F'  => 'Frankfurter',
        ];
    }

    public function asCommand(Command $command): int
    {
        $baseCurrency   = Currency::where('code', $command->argument('base_currency_code'))->firstOrFail();
        $targetCurrency = Currency::where('code', $command->argument('target_currency_code'))->firstOrFail();

        if ($baseCurrency->code == $targetCurrency->code) {
            $command->error('Same currency');

            return 1;
        }

        $date         = new Carbon($command->argument('date'));
        $exchangeData = $this->handle($baseCurrency, $targetCurrency, $date, $command->option('provider'));
        if ($exchangeData['status'] == 'error') {
            $command->error("Could not fetch exchange rate for {$baseCurrency->code}→{$targetCurrency->code} @ {$date->toDateString()}");

            return 1;
        }
        $command->info("Current exchange {$baseCurrency->code}→$targetCurrency->code @ {$date->toDateString()} (".$exchangeData['source'].")  : ".$exchangeData['exchange']);


        return 0;
    }
}
