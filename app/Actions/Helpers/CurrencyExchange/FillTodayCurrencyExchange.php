<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 18 Apr 2023 17:00:29 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\CurrencyExchange;

use App\Models\Helpers\Currency;
use App\Models\Helpers\CurrencyExchange;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class FillTodayCurrencyExchange
{
    use AsAction;

    public string $commandSignature = 'currency:fill-today';

    public function getCommandDescription(): string
    {
        return 'Fetch and store today currency exchange in all enabled currencies';
    }

    public function asCommand(Command $command): int
    {
        $exchangePivotCurrency = Currency::where('code', config('app.currency_exchange.pivot'))->first();

        $currencies = Currency::where('store_historic_data', true)->get();

        foreach ($currencies as $currency) {
            if ($currency->id == $exchangePivotCurrency->id) {
                continue;
            }

            $date=now();

            $exchangeData          = FetchCurrencyExchange::run($exchangePivotCurrency, $currency);
            $currencyExchangeValue = $exchangeData['exchange'] ?? null;
            if ($currencyExchangeValue) {
                $currencyExchange = CurrencyExchange::where('currency_id', $currency->id)->where('date', $date->toDateString())->first();

                if ($currencyExchange) {
                    $command->info("Updating $currency->code exchange: $currencyExchangeValue");

                    UpdateCurrencyExchange::run($currencyExchange, [
                        'exchange' => $currencyExchangeValue,
                        'source'   => $exchangeData['source'] ?? null,
                    ]);
                } else {
                    $command->info("Storing $currency->code exchange: $currencyExchangeValue");

                    StoreCurrencyExchange::run($currency, [
                        'exchange' => $currencyExchangeValue,
                        'date'     => gmdate('Y-m-d'),
                        'source'   => $exchangeData['source'] ?? null,
                    ]);
                }
            }
        }

        return 0;
    }
}
