<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 12:00:00 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment\UI;

use App\Actions\Traits\Dashboards\WithMarketingPeriod;
use App\Enums\DateIntervals\DateIntervalEnum;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Payment methods digest for the accounting dashboard, one per period the widget offers,
 * refreshed in the background every 6 hours so the dashboard only reads the cache.
 */
class GetPaymentMethodsPeriods
{
    use AsAction;
    use WithMarketingPeriod;

    public const array PERIODS = [DateIntervalEnum::ONE_MONTH, DateIntervalEnum::ONE_QUARTER, DateIntervalEnum::ONE_YEAR];

    public string $commandSignature = 'accounting:refresh-payment-methods-dashboard';

    /** @return array<string, array{summary: array<string, mixed>, period_label: string}> */
    public function handle(Organisation $organisation, bool $refresh = false): array
    {
        $key = "accounting-dashboard-payment-methods:$organisation->id";

        if ($refresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, now()->addHours(7), function () use ($organisation) {
            $periods = [];
            foreach (self::PERIODS as $interval) {
                $this->setMarketingPeriod(['selected_interval' => $interval->value]);
                $periods[$interval->value] = [
                    'summary'      => GetPaymentMethodsSummary::run($organisation, $this->periodFrom, $this->periodTo),
                    'period_label' => $this->periodLabels()['period_label'],
                ];
            }

            return $periods;
        });
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        Organisation::query()->each(fn (Organisation $organisation) => $this->handle($organisation, refresh: true));

        return 0;
    }
}
