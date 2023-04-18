<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:33 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\CurrencyExchange;

use App\Models\Assets\Currency;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class FillHistoricData
{
    use AsAction;

    public string $commandSignature = 'currency:fill';
    public string $commandDescription = 'Fill currency date';

    public function handle(): void
    {
        $currencies = Currency::where('store_historic_data', true)->get();

        foreach ($currencies as $currency) {
            $startDate = new Carbon($currency->historic_data_since);
            $endDate = now();
            $dateRange = CarbonPeriod::create($startDate, $endDate);
            $dates = array_map(fn($date) => $date->format('Y-m-d'), iterator_to_array($dateRange));

            foreach ($dates as $date) {
                $currencyValue = CurrencyExchange::run('USD', $currency->code, $date);

                if ($currencyValue) {
                    StoreCurrencyExchange::run([
                        'currency' => $currency->code,
                        'exchange' => $currencyValue,
                        'date' => $date,
                    ]);
                }
            }
        }
    }

    public function asCommand(Command $command): int
    {
        $this->handle();

        return 0;
    }
}
