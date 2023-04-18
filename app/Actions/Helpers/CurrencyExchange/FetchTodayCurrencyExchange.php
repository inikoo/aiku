<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 18 Apr 2023 17:00:29 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\CurrencyExchange;

use App\Models\Assets\Currency;
use App\Models\Helpers\CurrencyExchange;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchTodayCurrencyExchange
{
    use AsAction;

    public string $commandSignature = 'currency:fetch';

    public function getCommandDescription(): string
    {
        return 'Fetch and store today currency exchange in all enabled currencies';
    }

    public function asCommand(Command $command): int
    {
        $usd = Currency::where('code', 'USD')->first();

        $currencies = Currency::where('store_historic_data', true)->get();

        foreach ($currencies as $currency) {
            if ($currency->id == $usd->id) {
                continue;
            }


            $currencyExchangeValue = GetCurrencyExchange::run($usd, $currency);
            if ($currencyExchangeValue) {
                $currencyExchange = CurrencyExchange::where('currency_id', $currency->id)->where('date', gmdate('Y-m-d'))->first();

                if ($currencyExchange) {
                    $command->info("Updating $currency->code exchange: $currencyExchangeValue");

                    UpdateCurrencyExchange::run($currencyExchange, [
                        'exchange' => $currencyExchangeValue,
                    ]);
                } else {
                    $command->info("Storing $currency->code exchange: $currencyExchangeValue");

                    StoreCurrencyExchange::run($currency, [
                        'exchange' => $currencyExchangeValue,
                        'date'     => gmdate('Y-m-d'),
                    ]);
                }
            }
        }

        return 0;
    }
}
