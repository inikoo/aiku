<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 18 Apr 2023 17:00:29 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\CurrencyExchange;

use App\Models\Assets\Currency;
use App\Models\Helpers\CurrencyExchange;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchAllCurrencyExchange
{
    use AsAction;

    public string $commandSignature = 'currency:fetch-all';

    public function getCommandDescription(): string
    {
        return 'Fetch all historic data and store today currency exchange in all enabled currencies';
    }

    public function asCommand(Command $command): int
    {
        $usd = Currency::where('code', 'USD')->first();

        $currencies = Currency::where('store_historic_data', true)->get();

        foreach ($currencies as $currency) {
            if ($currency->id == $usd->id) {
                continue;
            }

            $startDate = new Carbon($currency->historic_data_since);
            $endDate   = now();
            $dateRange = CarbonPeriod::create($startDate, $endDate);
            $dates     = array_map(fn ($date) => $date->format('Y-m-d'), iterator_to_array($dateRange));

            $command->info("$currency->code from {$startDate->toDateString()}");


            foreach ($dates as $date) {
                if (!CurrencyExchange::where('currency', $currency->code)->where('date', $date)->exists()) {
                    $currencyValue = GetHistoricCurrencyExchange::run($usd, $currency, new Carbon($date));

                    if ($currencyValue) {
                        $command->info("$date $currency->code exchange: $currencyValue");

                        StoreCurrencyExchange::run($currency, [
                            'exchange' => $currencyValue,
                            'date'     => $date,
                        ]);
                    } else {
                        $command->error("$date $currency->code could not fetch");
                    }
                }
            }
        }

        return 0;
    }
}
