<?php

/*
 * Author: Steven Wicca <stewicalf@gmail.com>
 * Created: Thu, 13 Feb 2026 16:20:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Accounting\Reports\IntrastatExportTimeSeries;

use App\Enums\Accounting\Intrastat\IntrastatDeliveryTermsEnum;
use App\Enums\Accounting\Intrastat\IntrastatNatureOfTransactionEnum;
use App\Enums\Accounting\Intrastat\IntrastatTransportModeEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteTypeEnum;
use App\Enums\Helpers\TaxCategories\TaxCategoryTypeEnum;
use App\Enums\Helpers\TimeSeries\TimeSeriesFrequencyEnum;
use App\Helpers\IntrastatVatNumber;
use App\Helpers\TimeSeriesPeriodCalculator;
use App\Models\Helpers\Country;
use App\Models\SysAdmin\Organisation;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Support\Facades\DB;
use App\Actions\Traits\AsUniqueUntilProcessingAction;

class ProcessIntrastatExportTimeSeriesRecords implements ShouldBeUniqueUntilProcessing
{
    use AsUniqueUntilProcessingAction;

    /**
     * Dispatches of a period collapse into one run while it waits, and one arriving while it
     * runs queues the next run, so a delivery note dispatched mid-run is never left out.
     */
    public const int DEBOUNCE_SECONDS = 300;

    public string $jobQueue = 'sales_slave';


    public function getJobUniqueId(int $organisationId, TimeSeriesFrequencyEnum $frequency, string $from, string $to): string
    {
        return "$organisationId:$frequency->value:$from:$to";
    }

    public function handle(int $organisationId, TimeSeriesFrequencyEnum $frequency, string $from, string $to): void
    {
        [$from, $to] = TimeSeriesPeriodCalculator::expandWindowToFullPeriods($frequency, $from, $to);

        $organisation = Organisation::find($organisationId);

        if (!$organisation) {
            return;
        }

        $euCountryCodes = Country::getCountryCodesInEU();
        $euCountryIds   = Country::whereIn('code', $euCountryCodes)->where('id', '!=', $organisation->country_id)->pluck('id')->toArray();

        if (empty($euCountryIds)) {
            return;
        }

        $this->processTimeSeries($organisation, $euCountryIds, $frequency, $from, $to);
    }

    protected function processTimeSeries(Organisation $organisation, array $euCountryIds, TimeSeriesFrequencyEnum $frequency, string $from, string $to): void
    {
        $rawMetrics = DB::connection('aiku_no_sticky')->table('delivery_note_items as dni')
            ->join('delivery_notes as dn', 'dn.id', '=', 'dni.delivery_note_id')
            ->joinSub(
                DB::connection('aiku_no_sticky')->table('product_has_org_stocks as phos')
                    ->join('products as p', 'p.id', '=', 'phos.product_id')
                    ->select(
                        'phos.org_stock_id',
                        DB::raw('MIN(p.id) as product_id'),
                        DB::raw('MIN(p.tariff_code) as tariff_code'),
                        DB::raw('MIN(p.origin_country_id) as origin_country_id')
                    )
                    ->whereNotNull('p.tariff_code')
                    ->where('p.tariff_code', '!=', '')
                    ->groupBy('phos.org_stock_id'),
                'stock_products',
                'stock_products.org_stock_id',
                '=',
                'dni.org_stock_id'
            )
            ->leftJoin('transactions as t', 't.id', '=', 'dni.transaction_id')
            ->leftJoin('invoices as inv', 'inv.id', '=', 't.invoice_id')
            ->leftJoin('tax_categories as tc', 'tc.id', '=', 't.tax_category_id')
            ->join('countries as c', 'c.id', '=', 'dn.delivery_country_id')
            ->join('shops as s', 's.id', '=', 'dn.shop_id')
            ->where('dn.organisation_id', $organisation->id)
            ->where('dn.state', DeliveryNoteStateEnum::DISPATCHED)
            ->whereIn('dn.delivery_country_id', $euCountryIds)
            ->whereBetween('dn.dispatched_at', [$from, $to])
            ->select(
                'dn.id as delivery_note_id',
                'dn.type as delivery_note_type',
                's.type as shop_type',
                'stock_products.origin_country_id',
                'dn.dispatched_at',
                'stock_products.tariff_code',
                'dn.delivery_country_id as country_id',
                'c.code as country_code',
                't.tax_category_id',
                'tc.type as tax_category_type',
                'tc.rate as tax_category_rate',
                'inv.id as invoice_id',
                'inv.tax_number',
                'inv.tax_number_valid',
                'dni.quantity_dispatched',
                DB::raw('COALESCE(NULLIF(dni.org_revenue_amount, 0), t.org_net_amount, 0) as org_revenue_amount'),
                DB::raw('COALESCE(dni.estimated_picked_weight, 0) as item_weight'),
                'stock_products.product_id'
            )
            ->get();

        $this->store($organisation, $frequency, $from, $to, $this->aggregate($rawMetrics, $frequency));
    }

    /**
     * One row per period and Intrastat declaration line: tariff code, partner country, origin,
     * tax category, partner VAT and nature of transaction.
     */
    protected function aggregate(iterable $rawMetrics, TimeSeriesFrequencyEnum $frequency): array
    {
        $aggregated = [];

        foreach ($rawMetrics as $item) {
            $partnerTaxNumber    = $this->counterpartyVat($item);
            $natureOfTransaction = $this->natureOfTransaction($item);
            $tariffCodes         = array_map('trim', explode(',', $item->tariff_code));

            foreach ($tariffCodes as $tariffCode) {
                $tariffCode = str_replace(' ', '', trim($tariffCode));

                if (empty($tariffCode)) {
                    continue;
                }

                ['period' => $period, 'periodFrom' => $periodFrom, 'periodTo' => $periodTo] = TimeSeriesPeriodCalculator::resolvePeriodFromDate(Carbon::parse($item->dispatched_at), $frequency);

                $seriesKey = $this->seriesKey($tariffCode, $item->country_id, $item->origin_country_id, $item->tax_category_id, $partnerTaxNumber, $natureOfTransaction->value);
                $key       = $period.'|'.$seriesKey;

                if (!isset($aggregated[$key])) {
                    $aggregated[$key] = [
                        'series_key'            => $seriesKey,
                        'period'                => $period,
                        'period_from'           => $periodFrom,
                        'period_to'             => $periodTo,
                        'tariff_code'           => $tariffCode,
                        'country_id'            => $item->country_id,
                        'origin_country_id'     => $item->origin_country_id,
                        'tax_category_id'       => $item->tax_category_id,
                        'partner_tax_number'    => $partnerTaxNumber,
                        'nature_of_transaction' => $natureOfTransaction,
                        'delivery_note_type'    => $item->delivery_note_type,
                        'quantity'              => 0,
                        'value'                 => 0,
                        'weight'                => 0,
                        'delivery_notes'        => [],
                        'products'              => [],
                        'invoices'              => [],
                        'tax_numbers'           => [],
                    ];
                }

                $aggregated[$key]['quantity']                               += $item->quantity_dispatched ?? 0;
                $aggregated[$key]['value']                                  += $item->org_revenue_amount ?? 0;
                $aggregated[$key]['weight']                                 += $item->item_weight ?? 0;
                $aggregated[$key]['delivery_notes'][$item->delivery_note_id] = true;
                $aggregated[$key]['products'][$item->product_id]            = true;

                if ($item->invoice_id) {
                    $aggregated[$key]['invoices'][$item->invoice_id] = true;

                    if ($item->tax_number) {
                        $taxNumberKey = $item->tax_number . '_' . ($item->tax_number_valid ? 'valid' : 'invalid');

                        if (!isset($aggregated[$key]['tax_numbers'][$taxNumberKey])) {
                            $aggregated[$key]['tax_numbers'][$taxNumberKey] = [
                                'number' => $item->tax_number,
                                'valid'  => (bool) $item->tax_number_valid,
                            ];
                        }
                    }
                }
            }
        }

        return $aggregated;
    }

    /**
     * Rebuilds every period of the window: writes the records worked out from the dispatched
     * delivery notes and removes the ones of the window nothing was dispatched for any more.
     */
    protected function store(Organisation $organisation, TimeSeriesFrequencyEnum $frequency, string $from, string $to, array $aggregated): void
    {
        $seriesIds       = $this->seriesIds($organisation, $frequency, $aggregated);
        $frequencyLetter = $frequency->singleLetter();
        $now             = now()->toDateTimeString();

        $records = [];
        foreach ($aggregated as $data) {
            $partnerTaxNumbers = array_values($data['tax_numbers']);
            $validCount        = count(array_filter($partnerTaxNumbers, fn (array $taxNumber) => $taxNumber['valid']));

            $records[] = [
                'intrastat_export_time_series_id' => $seriesIds[$data['series_key']],
                'frequency'                       => $frequencyLetter,
                'period'                          => $data['period'],
                'organisation_id'                 => $organisation->id,
                'from'                            => $data['period_from']->toDateTimeString(),
                'to'                              => $data['period_to']->toDateTimeString(),
                'quantity'                        => $data['quantity'],
                'value_org_currency'              => $data['value'],
                'weight'                          => $data['weight'],
                'delivery_notes_count'            => count($data['delivery_notes']),
                'products_count'                  => count($data['products']),
                'delivery_note_type'              => $data['delivery_note_type'],
                'invoices_count'                  => count($data['invoices']),
                'partner_tax_numbers'             => empty($partnerTaxNumbers) ? null : json_encode($partnerTaxNumbers),
                'valid_tax_numbers_count'         => $validCount,
                'invalid_tax_numbers_count'       => count($partnerTaxNumbers) - $validCount,
                'mode_of_transport'               => IntrastatTransportModeEnum::ROAD->value,
                'delivery_terms'                  => IntrastatDeliveryTermsEnum::DAP->value,
                'nature_of_transaction'           => $data['nature_of_transaction']->value,
                'created_at'                      => $now,
                'updated_at'                      => $now,
            ];
        }

        $existingRecords = DB::table('intrastat_export_time_series_records')
            ->where('organisation_id', $organisation->id)
            ->where('frequency', $frequencyLetter)
            ->whereBetween('from', [$from, $to])
            ->get(['id', 'intrastat_export_time_series_id', 'period']);

        $written = [];
        foreach ($records as $record) {
            $written[$record['intrastat_export_time_series_id'].'|'.$record['period']] = true;
        }

        $staleRecords = $existingRecords->reject(fn (object $record) => isset($written[$record->intrastat_export_time_series_id.'|'.$record->period]));

        foreach (array_chunk($records, 1000) as $chunk) {
            DB::table('intrastat_export_time_series_records')->upsert(
                $chunk,
                ['intrastat_export_time_series_id', 'frequency', 'period'],
                array_diff(array_keys($chunk[0]), ['intrastat_export_time_series_id', 'frequency', 'period', 'created_at'])
            );
        }

        foreach ($staleRecords->pluck('id')->chunk(1000) as $staleIds) {
            DB::table('intrastat_export_time_series_records')
                ->where('frequency', $frequencyLetter)
                ->whereIn('id', $staleIds->all())
                ->delete();
        }

        $this->hydrateSeries(
            $frequencyLetter,
            array_unique([...array_column($records, 'intrastat_export_time_series_id'), ...$staleRecords->pluck('intrastat_export_time_series_id')->all()])
        );
    }

    /**
     * @return array<string, int> series id by series key, creating the series that are new
     */
    protected function seriesIds(Organisation $organisation, TimeSeriesFrequencyEnum $frequency, array $aggregated): array
    {
        $existing = fn () => DB::table('intrastat_export_time_series')
            ->where('organisation_id', $organisation->id)
            ->where('frequency', $frequency->value)
            ->get(['id', 'tariff_code', 'country_id', 'origin_country_id', 'tax_category_id', 'partner_tax_number', 'nature_of_transaction'])
            ->mapWithKeys(fn (object $series) => [
                $this->seriesKey($series->tariff_code, $series->country_id, $series->origin_country_id, $series->tax_category_id, $series->partner_tax_number, $series->nature_of_transaction) => $series->id,
            ])
            ->all();

        $seriesIds = $existing();
        $now       = now()->toDateTimeString();

        $missing = [];
        foreach ($aggregated as $data) {
            if (!isset($seriesIds[$data['series_key']]) && !isset($missing[$data['series_key']])) {
                $missing[$data['series_key']] = [
                    'organisation_id'       => $organisation->id,
                    'tariff_code'           => $data['tariff_code'],
                    'country_id'            => $data['country_id'],
                    'origin_country_id'     => $data['origin_country_id'],
                    'tax_category_id'       => $data['tax_category_id'],
                    'partner_tax_number'    => $data['partner_tax_number'],
                    'nature_of_transaction' => $data['nature_of_transaction']->value,
                    'frequency'             => $frequency->value,
                    'data'                  => '[]',
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];
            }
        }

        if ($missing) {
            foreach (array_chunk(array_values($missing), 1000) as $chunk) {
                DB::table('intrastat_export_time_series')->insertOrIgnore($chunk);
            }
            $seriesIds = $existing();
        }

        return $seriesIds;
    }

    /**
     * Number of records and first and last day of each series, written only where they changed.
     */
    protected function hydrateSeries(string $frequencyLetter, array $seriesIds): void
    {
        foreach (array_chunk($seriesIds, 5000) as $chunk) {
            DB::update(
                'UPDATE intrastat_export_time_series AS series
                SET number_records = records.number_records, "from" = records.first_day, "to" = records.last_day, updated_at = now()
                FROM (
                    SELECT ids.id, COUNT(record.id) AS number_records, MIN(record."from")::date AS first_day, MAX(record."to")::date AS last_day
                    FROM unnest(?::int[]) AS ids(id)
                    LEFT JOIN intrastat_export_time_series_records AS record
                        ON record.intrastat_export_time_series_id = ids.id AND record.frequency = ?
                    GROUP BY ids.id
                ) AS records
                WHERE series.id = records.id
                  AND (series.number_records, series."from", series."to") IS DISTINCT FROM (records.number_records, records.first_day, records.last_day)',
                ['{'.implode(',', $chunk).'}', $frequencyLetter]
            );
        }
    }

    protected function seriesKey(string $tariffCode, mixed $countryId, mixed $originCountryId, mixed $taxCategoryId, ?string $partnerTaxNumber, ?string $natureOfTransaction): string
    {
        return $tariffCode.'|'.$countryId.'|'.($originCountryId ?? 'null').'|'.($taxCategoryId ?? 'null').'|'.($partnerTaxNumber ?? 'null').'|'.($natureOfTransaction ?? 'null');
    }

    protected function natureOfTransaction(object $item): IntrastatNatureOfTransactionEnum
    {
        return match (true) {
            $item->delivery_note_type === DeliveryNoteTypeEnum::REPLACEMENT->value => IntrastatNatureOfTransactionEnum::RETURN_REPLACEMENT,
            $item->shop_type === ShopTypeEnum::DROPSHIPPING->value                 => IntrastatNatureOfTransactionEnum::PRIVATE_CONSUMER,
            default                                                                => IntrastatNatureOfTransactionEnum::OUTRIGHT_PURCHASE,
        };
    }

    protected function counterpartyVat(object $item): ?string
    {
        $zeroRatedIntraEu = $item->tax_category_type === TaxCategoryTypeEnum::EU_VTC->value
            && (float) ($item->tax_category_rate ?? 0) == 0.0;

        if (!$zeroRatedIntraEu) {
            return null;
        }

        return IntrastatVatNumber::normalise($item->tax_number, $item->country_code);
    }
}
