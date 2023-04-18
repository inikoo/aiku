<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 10:28:39 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\CurrencyExchange;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use AmrShawky\LaravelCurrency\Facade\Currency as FetchCurrency;
use App\Models\Assets\Currency;

class GetHistoricCurrencyExchange
{
    use AsAction;

    public string $commandSignature = 'currency:historic-exchange {base_currency_code} {target_currency_code} {date}';

    public function handle(Currency $baseCurrency, Currency $targetCurrency, Carbon $date): float|null
    {
        if($baseCurrency->code==$targetCurrency) {
            return 1;
        }

        return FetchCurrency::convert()
            ->from($baseCurrency->code)
            ->to($targetCurrency->code)
            ->date($date->toDateString())
            ->get();
    }


    public function asCommand(Command $command): int
    {
        $baseCurrency  =Currency::where('code', $command->argument('base_currency_code'))->firstOrFail();
        $targetCurrency=Currency::where('code', $command->argument('target_currency_code'))->firstOrFail();

        if($baseCurrency->code==$targetCurrency->code) {
            $command->error('Same currency');
            return 1;
        }

        $date    =new Carbon($command->argument('date'));
        $exchange=$this->handle($baseCurrency, $targetCurrency, $date);

        $command->info("Current exchange {$baseCurrency->code}→$targetCurrency->code @ {$date->toDateString()} : $exchange");


        return 0;
    }
}
