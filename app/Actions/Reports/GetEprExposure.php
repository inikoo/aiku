<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Reports;

use App\Enums\Goods\Packaging\EprSchemeEnum;
use App\Models\Helpers\Country;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Where an organisation's sales packaging goes: kg dispatched per destination country and month, split into parcels to
 * consumers (which make the organisation the producer in a distance-selling country) and to trade customers (who
 * usually import and report it themselves), so exposure to another country's scheme is seen while it builds up.
 * A country with a scheme that receives consumer parcels, or the organisation's own country, is flagged until a
 * registration is recorded for it.
 */
class GetEprExposure
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(Organisation $organisation, Carbon $from, Carbon $to): array
    {
        $consumer = GetEuPackagingReturn::CONSUMER_CHANNEL_SQL;
        $rows     = DB::select(
            <<<SQL
            SELECT l.counterparty_country_id AS country_id, TO_CHAR(l.date, 'YYYY-MM') AS month, $consumer AS consumer,
                SUM(l.quantity * pc.weight_g * fhc.quantity_per_unit) / 1000 AS kg
            FROM epr_flow_lines l
            LEFT JOIN org_stocks os ON os.id = l.org_stock_id
            JOIN trade_units tu ON tu.id = l.trade_unit_id
            JOIN packaging_families pf ON pf.id = tu.packaging_family_id AND NOT pf.is_product_itself
            JOIN packaging_family_has_components fhc ON fhc.packaging_family_id = pf.id
            JOIN packaging_components pc ON pc.id = fhc.packaging_component_id
            WHERE l.organisation_id = ? AND l.date BETWEEN ? AND ?
              AND l.activity IN ('sold_domestic', 'exported', 'sale_unknown_country')
              AND NOT COALESCE(os.is_shipment_packaging, FALSE)
              AND pc.weight_g > 0
            GROUP BY 1, 2, 3
            SQL,
            [$organisation->id, $from->toDateString(), $to->toDateString()]
        );

        $months = [];
        for ($month = $from->copy()->startOfMonth(); $month->lte($to); $month->addMonth()) {
            $months[] = $month->format('Y-m');
        }

        $byCountry = [];
        $unknownKg = 0.0;
        foreach ($rows as $row) {
            if ($row->country_id === null) {
                $unknownKg += (float)$row->kg;
                continue;
            }
            $byCountry[$row->country_id] ??= ['consumer_kg' => 0.0, 'trade_kg' => 0.0, 'months' => array_fill_keys($months, 0.0)];
            $byCountry[$row->country_id][$row->consumer ? 'consumer_kg' : 'trade_kg'] += (float)$row->kg;
            $byCountry[$row->country_id]['months'][$row->month] = ($byCountry[$row->country_id]['months'][$row->month] ?? 0) + (float)$row->kg;
        }

        $registrations = $organisation->settings['epr_registrations'] ?? [];
        $countries     = Country::whereIn('id', array_keys($byCountry))->get(['id', 'code', 'name'])->keyBy('id');
        $total         = array_sum(array_map(fn (array $country) => $country['consumer_kg'] + $country['trade_kg'], $byCountry)) + $unknownKg;

        $list = [];
        foreach ($byCountry as $countryId => $values) {
            $country      = $countries->get($countryId);
            $own          = $countryId === $organisation->country_id;
            $kg           = $values['consumer_kg'] + $values['trade_kg'];
            $hasScheme    = in_array($country?->code, EprSchemeEnum::COUNTRIES_WITH_SCHEMES);
            $registration = $registrations[$country?->code] ?? null;
            $list[]       = [
                'country_id'   => $countryId,
                'code'         => $country?->code,
                'name'         => $country?->name,
                'own'          => $own,
                'has_scheme'   => $hasScheme,
                'consumer_kg'  => round($values['consumer_kg'], 1),
                'trade_kg'     => round($values['trade_kg'], 1),
                'kg'           => round($kg, 1),
                'share'        => $total > 0 ? round($kg / $total * 100, 1) : 0.0,
                'months'       => array_map(fn (float $monthKg) => round($monthKg, 1), $values['months']),
                'registration' => $registration,
                'unregistered' => $hasScheme && !$registration && ($own || $values['consumer_kg'] > 0),
            ];
        }
        usort($list, fn (array $a, array $b) => [$b['own'], $b['kg']] <=> [$a['own'], $a['kg']]);

        return [
            'from'                => $from->toDateString(),
            'to'                  => $to->toDateString(),
            'has_data'            => $list !== [] || $unknownKg > 0,
            'months'              => $months,
            'kg'                  => round($total, 1),
            'abroad_consumer_kg'  => round(array_sum(array_map(fn (array $country) => $country['own'] ? 0 : $country['consumer_kg'], $list)), 1),
            'abroad_trade_kg'     => round(array_sum(array_map(fn (array $country) => $country['own'] ? 0 : $country['trade_kg'], $list)), 1),
            'unknown_country_kg'  => round($unknownKg, 1),
            'unregistered'        => count(array_filter($list, fn (array $country) => $country['unregistered'])),
            'countries'           => $list,
        ];
    }
}
