<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\CurrencyExchange;

use App\Models\Helpers\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class WarmCurrencyExchanges
{
    use AsAction;

    public const string PAIRS_KEY = 'currency-exchange-pairs';

    public string $commandSignature = 'currency:warm';

    public function getCommandDescription(): string
    {
        return 'Refresh the cached exchange of every currency pair the application has asked for, so no page waits for the provider';
    }

    /**
     * @return array<string, float|null> the fresh exchange per pair, null where the provider gave none and the cached one was left alone
     */
    public function handle(): array
    {
        $pairs      = Cache::get(self::PAIRS_KEY, []);
        $currencies = Currency::whereIn('code', collect($pairs)->flatMap(fn (string $pair) => explode('-', $pair))->unique())->get()->keyBy('code');

        $refreshed = [];
        foreach ($pairs as $pair) {
            [$baseCode, $targetCode] = explode('-', $pair);

            try {
                $exchange = FetchCurrencyExchange::run($currencies[$baseCode], $currencies[$targetCode])['exchange'] ?? null;
            } catch (Throwable) {
                $exchange = null;
            }

            if ($exchange) {
                Cache::put('current-currency-exchange:'.$pair, $exchange, now()->addHours(6));
            }
            $refreshed[$pair] = $exchange ? (float) $exchange : null;
        }

        return $refreshed;
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        foreach ($this->handle() as $pair => $exchange) {
            $command->line($pair.' '.($exchange ?? 'no answer, kept the cached one'));
        }

        return 0;
    }
}
