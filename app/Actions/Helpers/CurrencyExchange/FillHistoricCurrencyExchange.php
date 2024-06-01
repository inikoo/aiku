<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 18 Apr 2023 17:00:29 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\CurrencyExchange;

use App\Models\Helpers\Currency;
use App\Models\Helpers\CurrencyExchange;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class FillHistoricCurrencyExchange
{
    use AsAction;

    public string $commandSignature = 'currency:fill-historic {--c|currency=} {--f|from=}';

    public function getCommandDescription(): string
    {
        return 'Fetch all historic data and store today currency exchange in all enabled currencies';
    }

    public function asCommand(Command $command): int
    {
        $exchangePivotCurrency = Currency::where('code', config('app.currency_exchange.pivot'))->first();

        $currencies = Currency::where('store_historic_data', true);
        if ($command->option('currency')) {
            $currencies->where('code', $command->option('currency'));
        }


        foreach ($currencies->get() as $currency) {
            if ($currency->id == $exchangePivotCurrency->id) {
                continue;
            }


            if($command->option('from')) {
                $startDate = new Carbon($command->option('from'));
            } else {
                $startDate = new Carbon($currency->historic_data_since);
            }

            $endDate   = Carbon::yesterday();
            $dateRange = CarbonPeriod::create($startDate, $endDate);
            $dates     = array_map(fn ($date) => $date->format('Y-m-d'), iterator_to_array($dateRange));

            $command->info("$currency->code from {$startDate->toDateString()}");


            foreach ($dates as $date) {
                if (!CurrencyExchange::where('currency_id', $currency->id)->where('date', $date)->exists()) {
                    $exchangeData = FetchCurrencyExchange::run($exchangePivotCurrency, $currency, new Carbon($date));


                    $currencyValue = $exchangeData['exchange'] ?? null;
                    if ($currencyValue) {
                        $command->info("$date $currency->code exchange: $currencyValue");

                        StoreCurrencyExchange::run($currency, [
                            'exchange' => $currencyValue,
                            'date'     => $date,
                            'source'   => $exchangeData['source'] ?? null
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
