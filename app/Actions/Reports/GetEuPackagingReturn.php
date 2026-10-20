<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Oct 2026 18:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Reports;

use App\Enums\Goods\Packaging\EprSchemeEnum;
use App\Enums\Goods\Packaging\PackagingMaterialCategoryEnum;
use App\Enums\Goods\Packaging\PackagingPolymerEnum;
use App\Models\Goods\EprMaterialMapping;
use App\Models\Helpers\Country;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * The packaging an organisation placed on one country's market in a period, in the materials of that country's scheme.
 *
 * For its own country (Slovakia reports quarterly to its OZV) that is the sales packaging of everything it dispatched to
 * addresses there. For another country it is distance selling: only parcels to consumers (dropshipping, retail and
 * marketplace orders) count, because a trade customer who receives the goods is usually the importer and reports them
 * itself; those are totalled apart so the split can be checked. Both add an estimate of the shipment packaging the
 * parcels went out in: the shipment packaging used in the period times the share of parcels that count. Plastics
 * without a polymer take the scheme's general plastic line. In its own country it also reports, as importer, the packing
 * material that came inside the cartons it imported, from the agents' packing specs; distance selling does not.
 */
class GetEuPackagingReturn
{
    use AsAction;

    public const string CONSUMER_CHANNEL_SQL = "(l.shop_type IN ('dropshipping', 'b2c') OR l.platform_id IS NOT NULL)";

    public function scheme(Organisation $organisation): ?EprSchemeEnum
    {
        $scheme = EprSchemeEnum::tryFrom(mb_strtolower((string)$organisation->country?->code));

        return $scheme === EprSchemeEnum::UK ? null : $scheme;
    }

    /**
     * The schemes a return can be produced for, the organisation's own first.
     *
     * @return list<EprSchemeEnum>
     */
    public function schemes(Organisation $organisation): array
    {
        $own = $this->scheme($organisation);

        return array_values(array_filter(
            [$own, ...array_filter(EprSchemeEnum::cases(), fn (EprSchemeEnum $scheme) => $scheme !== $own)],
            fn (?EprSchemeEnum $scheme) => $scheme && $scheme !== EprSchemeEnum::UK
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Organisation $organisation, Carbon $from, Carbon $to, ?EprSchemeEnum $scheme = null): array
    {
        $scheme ??= $this->schemes($organisation)[0];
        $countryId = Country::where('code', $scheme->countryCode())->value('id');
        $domestic  = $countryId === $organisation->country_id;
        $current   = $this->materials($organisation, $scheme, $countryId, $domestic, $from, $to);
        $days      = (int)$from->diffInDays($to) + 1;
        $previous  = $this->materials($organisation, $scheme, $countryId, $domestic, $from->copy()->subDays($days), $from->copy()->subDay());

        $rows = [];
        foreach (array_unique([...array_keys($current['kg']), ...array_keys($previous['kg'])]) as $key) {
            [$material, $subcategory] = explode('|', $key);
            $kg          = $current['kg'][$key] ?? 0.0;
            $previousKg  = isset($previous['kg'][$key]) ? $previous['kg'][$key] : ($previous['has_data'] ? 0.0 : null);
            $rows[]      = [
                'scheme_material'    => $material,
                'scheme_subcategory' => $subcategory ?: null,
                'sales_kg'           => round($current['sales'][$key] ?? 0.0, 1),
                'shipment_kg'        => round($current['shipment'][$key] ?? 0.0, 1),
                'inbound_kg'         => round($current['inbound'][$key] ?? 0.0, 1),
                'kg'                 => round($kg, 1),
                'previous_kg'        => $previousKg === null ? null : round($previousKg, 1),
                'change'             => $previousKg ? round(($kg - $previousKg) / $previousKg * 100, 1) : null,
            ];
        }
        usort($rows, fn (array $a, array $b) => $b['kg'] <=> $a['kg']);

        return [
            'scheme'                  => $scheme->value,
            'scheme_label'            => EprSchemeEnum::labels()[$scheme->value],
            'schemes'                 => array_map(fn (EprSchemeEnum $option) => ['value' => $option->value, 'label' => EprSchemeEnum::labels()[$option->value]], $this->schemes($organisation)),
            'domestic'                => $domestic,
            'from'                    => $from->toDateString(),
            'to'                      => $to->toDateString(),
            'has_data'                => $current['has_data'],
            'kg'                      => round(array_sum($current['kg']), 1),
            'previous_kg'             => $previous['has_data'] ? round(array_sum($previous['kg']), 1) : null,
            'rows'                    => $rows,
            'trade_kg'                => round($current['trade_kg'], 1),
            'parcel_share'            => $current['parcel_share'],
            'plastic_without_polymer' => round($current['plastic_without_polymer'], 1),
            'units_without_weights'   => $current['units_without_weights'],
            'units'                   => $current['units'],
        ];
    }

    /**
     * @return array{kg: array<string, float>, sales: array<string, float>, shipment: array<string, float>, inbound: array<string, float>, has_data: bool, parcel_share: float|null, trade_kg: float, plastic_without_polymer: float, units: float, units_without_weights: float}
     */
    private function materials(Organisation $organisation, EprSchemeEnum $scheme, ?int $countryId, bool $domestic, Carbon $from, Carbon $to): array
    {
        $consumer = self::CONSUMER_CHANNEL_SQL;
        $sold     = $domestic
            ? "l.activity = 'sold_domestic'"
            : "l.activity = 'exported' AND l.counterparty_country_id = ".(int)$countryId;
        $counts   = $domestic ? 'TRUE' : $consumer;
        $bindings = [$organisation->id, $from->toDateString(), $to->toDateString()];

        $weights = DB::select(
            <<<SQL
            SELECT CASE WHEN l.activity = 'shipment_packaging' THEN 'shipment' WHEN $counts THEN 'sales' ELSE 'trade' END AS bucket,
                pc.material_category, pc.polymer, SUM(l.quantity * pc.weight_g * fhc.quantity_per_unit) / 1000 AS kg
            FROM epr_flow_lines l
            LEFT JOIN org_stocks os ON os.id = l.org_stock_id
            JOIN trade_units tu ON tu.id = l.trade_unit_id
            JOIN packaging_families pf ON pf.id = tu.packaging_family_id AND NOT pf.is_product_itself
            JOIN packaging_family_has_components fhc ON fhc.packaging_family_id = pf.id
            JOIN packaging_components pc ON pc.id = fhc.packaging_component_id
            WHERE l.organisation_id = ? AND l.date BETWEEN ? AND ?
              AND pc.weight_g > 0
              AND (($sold AND NOT COALESCE(os.is_shipment_packaging, FALSE))
                OR (l.activity = 'shipment_packaging' AND COALESCE(os.is_shipment_packaging, FALSE)))
            GROUP BY 1, pc.material_category, pc.polymer
            SQL,
            $bindings
        );

        $units = DB::selectOne(
            <<<SQL
            SELECT SUM(l.quantity) AS units,
                SUM(l.quantity) FILTER (WHERE NOT EXISTS (SELECT 1 FROM packaging_family_has_components c WHERE c.packaging_family_id = tu.packaging_family_id)) AS without_weights
            FROM epr_flow_lines l
            LEFT JOIN trade_units tu ON tu.id = l.trade_unit_id
            WHERE l.organisation_id = ? AND l.date BETWEEN ? AND ? AND $sold AND $counts
            SQL,
            $bindings
        );

        $inbound = $domestic ? DB::select(
            <<<'SQL'
            SELECT 'inbound' AS bucket, material_category, polymer, SUM(kg) AS kg
            FROM epr_packing_lines
            WHERE organisation_id = ? AND date BETWEEN ? AND ?
              AND activity IN ('imported', 'imported_discarded', 'reused')
            GROUP BY material_category, polymer
            SQL,
            $bindings
        ) : [];

        $parcelsCount = $domestic ? 'TRUE' : str_replace('l.', 'dn.', $consumer);
        $parcels      = DB::selectOne(
            <<<SQL
            SELECT COUNT(*) AS all_notes, COUNT(*) FILTER (WHERE dn.delivery_country_id = ? AND $parcelsCount) AS counted
            FROM delivery_notes dn
            WHERE dn.warehouse_id IN (SELECT id FROM warehouses WHERE organisation_id = ?)
              AND dn.organisation_id = ? AND dn.deleted_at IS NULL AND dn.state = 'dispatched'
              AND dn.dispatched_at >= ?::date AND dn.dispatched_at < ?::date + 1
            SQL,
            [$countryId, $organisation->id, ...$bindings]
        );
        $share = $parcels->all_notes > 0 ? $parcels->counted / $parcels->all_notes : null;

        $result = ['kg' => [], 'sales' => [], 'shipment' => [], 'inbound' => [], 'has_data' => false, 'parcel_share' => $share === null ? null : round($share * 100, 1), 'trade_kg' => 0.0,
                   'plastic_without_polymer' => 0.0, 'units' => (float)$units->units, 'units_without_weights' => (float)$units->without_weights];

        $mappings = [];
        foreach ([...$weights, ...$inbound] as $row) {
            if ($row->bucket === 'trade') {
                $result['trade_kg'] += (float)$row->kg;
                continue;
            }
            $kg = (float)$row->kg * ($row->bucket === 'shipment' ? ($share ?? 0) : 1);
            if ($kg <= 0) {
                continue;
            }
            $result['has_data'] = true;
            $mappingKey = $row->material_category.'|'.$row->polymer;
            $mappings[$mappingKey] ??= EprMaterialMapping::resolve($scheme, PackagingMaterialCategoryEnum::from($row->material_category), $row->polymer ? PackagingPolymerEnum::from($row->polymer) : null);
            $mapping = $mappings[$mappingKey];
            $key     = ($mapping?->scheme_material ?? $row->material_category).'|'.$mapping?->scheme_subcategory;

            $result[$row->bucket][$key] = ($result[$row->bucket][$key] ?? 0) + $kg;
            $result['kg'][$key]         = ($result['kg'][$key] ?? 0) + $kg;
            if ($row->material_category === 'plastic' && !$row->polymer) {
                $result['plastic_without_polymer'] += $kg;
            }
        }

        return $result;
    }
}
