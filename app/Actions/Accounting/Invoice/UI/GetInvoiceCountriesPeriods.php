<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 22 Oct 2026 00:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\UI;

use App\Actions\Accounting\Invoice\WithInvoiceDeliveryCountry;
use App\Actions\Traits\Dashboards\WithMarketingPeriod;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Enums\DateIntervals\DateIntervalEnum;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Invoices by delivery country for the accounting dashboard, one digest per period the widget offers,
 * refreshed in the background every 6 hours so the dashboard only reads the cache.
 */
class GetInvoiceCountriesPeriods
{
    use AsAction;
    use WithMarketingPeriod;
    use WithInvoiceDeliveryCountry;

    public const array PERIODS = [DateIntervalEnum::ONE_MONTH, DateIntervalEnum::ONE_QUARTER, DateIntervalEnum::ONE_YEAR];

    private const int TOP_COUNTRIES = 6;

    public string $commandSignature = 'accounting:refresh-invoice-countries-dashboard';

    /** @return array<string, array{period_label: string, between: string, number_invoices: int, others: int, countries: list<array{country_code: ?string, country_name: ?string, number_invoices: int, share: float}>}> */
    public function handle(Organisation $organisation, bool $refresh = false): array
    {
        $key = "accounting-dashboard-invoice-countries:$organisation->id";

        if ($refresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, now()->addHours(7), function () use ($organisation) {
            $periods = [];
            foreach (self::PERIODS as $interval) {
                $this->setMarketingPeriod(['selected_interval' => $interval->value]);
                $from = $this->periodFrom;
                $to   = $this->periodTo ?? now()->endOfDay();

                $query = DB::table('invoices')
                    ->where('invoices.organisation_id', $organisation->id)
                    ->where('invoices.type', InvoiceTypeEnum::INVOICE)
                    ->where('invoices.in_process', false)
                    ->whereNull('invoices.deleted_at')
                    ->whereBetween('invoices.date', [$from, $to]);
                $this->joinInvoiceDeliveryCountry($query);

                $rows = $query->select([
                    'delivery_countries.code as country_code',
                    'delivery_countries.name as country_name',
                    DB::raw('count(*) as number_invoices'),
                ])
                    ->groupBy('delivery_countries.code', 'delivery_countries.name')
                    ->orderByDesc('number_invoices')
                    ->get();

                $numberInvoices = (int) $rows->sum('number_invoices');

                $periods[$interval->value] = [
                    'period_label'    => $this->periodLabels()['period_label'],
                    'between'         => $from->format('Ymd').'-'.$to->format('Ymd'),
                    'number_invoices' => $numberInvoices,
                    'others'          => max(0, $rows->count() - self::TOP_COUNTRIES),
                    'countries'       => $rows->take(self::TOP_COUNTRIES)->map(fn ($row) => [
                        'country_code'    => $row->country_code,
                        'country_name'    => $row->country_name,
                        'number_invoices' => (int) $row->number_invoices,
                        'share'           => $numberInvoices ? round(100 * $row->number_invoices / $numberInvoices, 1) : 0,
                    ])->values()->all(),
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
