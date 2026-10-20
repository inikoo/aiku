<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 12:30
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Reports;

use App\Actions\SupplyChain\PackingSpec\GetPurchaseOrderPackingSpecGaps;
use App\Enums\Goods\Packaging\EprSchemeEnum;
use App\Enums\Goods\Packaging\PackagingMaterialCategoryEnum;
use App\Enums\SysAdmin\Authorisation\GroupPermissionsEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecLevelEnum;
use App\Models\Goods\EprMaterialMapping;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * The packing material that came inside the cartons of what an organisation received in a period, from its inbound
 * packing lines: kg by activity, level and material (named as its scheme names it), with the supplier products and
 * deliveries behind them; how much of what arrived has a verified, a declared or no packing spec, with the material of
 * the undeclared estimated from the carton weight less the net weight, else from the kg per carton of what was
 * declared; and how far each agent is with its declarations. Material passed on to the buyer that the product's own
 * packaging already weighs is shown apart and left out of the kg, as the returns leave it out.
 */
class GetEprInboundPacking
{
    use AsAction;

    public const array ACTIVITIES = ['imported', 'imported_discarded', 'reused', 'bought_domestic', 'purchase_unknown_origin', 'packed_filled'];

    public const int ROWS = 100;

    public const int LEAGUE_MONTHS = 12;

    /**
     * @return array<string, mixed>
     */
    public function handle(Organisation $organisation, Carbon $from, Carbon $to, ?User $user = null): array
    {
        $bindings = [$organisation->id, $from->toDateString(), $to->toDateString()];
        $countryCode = $organisation->country?->code;
        $scheme      = $countryCode === 'GB' ? EprSchemeEnum::UK : EprSchemeEnum::tryFrom(mb_strtolower((string)$countryCode));

        $rows = DB::select(
            <<<'SQL'
            SELECT activity, level, material_category, SUM(kg) AS kg, BOOL_AND(weight_is_measured) AS all_measured, SUM(kg) FILTER (WHERE weight_is_measured) AS measured_kg
            FROM epr_packing_lines
            WHERE organisation_id = ? AND date BETWEEN ? AND ? AND NOT in_product_packaging
            GROUP BY activity, level, material_category
            SQL,
            $bindings
        );

        $materialName = function (string $category) use ($scheme): string {
            $label = PackagingMaterialCategoryEnum::labels()[$category] ?? $category;
            if (!$scheme) {
                return $label;
            }
            $mapping = EprMaterialMapping::resolve($scheme, PackagingMaterialCategoryEnum::from($category));

            return $mapping ? trim($mapping->scheme_material.($mapping->scheme_code ? " ($mapping->scheme_code)" : '')) : $label;
        };

        $materials = collect($rows)->pluck('material_category')->unique()->sort()->values();
        $matrix    = collect($rows)->map(fn ($row) => [
            'activity' => $row->activity,
            'level'    => $row->level,
            'material' => $row->material_category,
            'kg'       => round((float)$row->kg, 2),
        ])->values();

        return [
            'from'          => $from->toDateString(),
            'to'            => $to->toDateString(),
            'scheme'        => $scheme?->value,
            'scheme_label'  => $scheme ? EprSchemeEnum::labels()[$scheme->value] : null,
            'has_data'      => $rows !== [],
            'kg'            => round(collect($rows)->sum(fn ($row) => (float)$row->kg), 1),
            'measured_kg'   => round(collect($rows)->sum(fn ($row) => (float)$row->measured_kg), 1),
            'in_product_packaging_kg' => round((float)DB::selectOne(
                'SELECT SUM(kg) AS kg FROM epr_packing_lines WHERE organisation_id = ? AND date BETWEEN ? AND ? AND in_product_packaging',
                $bindings
            )->kg, 1),
            'activities'    => collect(self::ACTIVITIES)->mapWithKeys(fn (string $activity) => [$activity => $this->activityLabel($activity)])->all(),
            'levels'        => PackingSpecLevelEnum::labels(),
            'materials'     => $materials->mapWithKeys(fn (string $category) => [$category => $materialName($category)])->all(),
            'matrix'        => $matrix->all(),
            'products'      => $this->products($bindings),
            'deliveries'    => $this->deliveries($bindings),
            'coverage'      => $this->coverage($bindings),
            'league'        => $this->league($organisation, $user),
        ];
    }

    public function activityLabel(string $activity): string
    {
        return match ($activity) {
            'imported'                => __('Imported, passed on'),
            'imported_discarded'      => __('Imported, discarded at our warehouse'),
            'reused'                  => __('Imported, reused for dispatch'),
            'bought_domestic'         => __('Bought domestically'),
            'purchase_unknown_origin' => __('Bought, origin unknown'),
            'packed_filled'           => __('Own production'),
            default                   => $activity,
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function products(array $bindings): array
    {
        return collect(DB::select(
            <<<'SQL'
            SELECT l.supplier_product_id, sp.code, sp.name, a.code AS agent, SUM(l.kg) AS kg,
                MAX(l.spec_status) AS spec_status, BOOL_AND(l.weight_is_measured) AS measured,
                (SELECT osp.slug FROM org_supplier_products osp WHERE osp.supplier_product_id = l.supplier_product_id AND osp.organisation_id = l.organisation_id LIMIT 1) AS org_supplier_product_slug
            FROM epr_packing_lines l
            JOIN supplier_products sp ON sp.id = l.supplier_product_id
            LEFT JOIN agents a ON a.id = l.agent_id
            WHERE l.organisation_id = ? AND l.date BETWEEN ? AND ? AND NOT l.in_product_packaging
            GROUP BY l.supplier_product_id, l.organisation_id, sp.code, sp.name, a.code
            ORDER BY kg DESC
            LIMIT 100
            SQL,
            $bindings
        ))->map(fn ($row) => [
            'code'                      => $row->code,
            'name'                      => $row->name,
            'agent'                     => $row->agent,
            'kg'                        => round((float)$row->kg, 2),
            'spec_status'               => $row->spec_status,
            'measured'                  => (bool)$row->measured,
            'org_supplier_product_slug' => $row->org_supplier_product_slug,
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function deliveries(array $bindings): array
    {
        return collect(DB::select(
            <<<'SQL'
            SELECT sd.id, sd.reference, sd.slug, MIN(l.date) AS date, a.code AS agent, SUM(l.kg) AS kg, COUNT(DISTINCT l.supplier_product_id) AS products
            FROM epr_packing_lines l
            JOIN stock_deliveries sd ON sd.id = l.stock_delivery_id
            LEFT JOIN agents a ON a.id = l.agent_id
            WHERE l.organisation_id = ? AND l.date BETWEEN ? AND ? AND NOT l.in_product_packaging
            GROUP BY sd.id, sd.reference, sd.slug, a.code
            ORDER BY date DESC
            LIMIT 100
            SQL,
            $bindings
        ))->map(fn ($row) => [
            'reference' => $row->reference,
            'slug'      => $row->slug,
            'date'      => $row->date,
            'agent'     => $row->agent,
            'products'  => (int)$row->products,
            'kg'        => round((float)$row->kg, 2),
        ])->all();
    }

    /**
     * What arrived in the period by the state of its packing spec, in cartons and kg of packing material; without a
     * spec the kg is the carton weight less the net weight, where the supplier product has both.
     *
     * @return array<string, mixed>
     */
    private function coverage(array $bindings): array
    {
        $rows = DB::select(
            <<<'SQL'
            SELECT sdi.id, sdi.supplier_product_id, sp.code, sp.name, a.code AS agent,
                flow.units / GREATEST(COALESCE(hsp.units_per_carton, sp.units_per_carton, 1), 1) AS cartons,
                CASE
                    WHEN spec.id IS NULL THEN 'no_spec'
                    WHEN spec.status = 'verified' OR spec.verified_at IS NOT NULL THEN 'verified'
                    ELSE 'declared'
                END AS bucket,
                CASE WHEN sp.carton_weight > sp.carton_net_weight AND sp.carton_net_weight > 0 THEN (sp.carton_weight - sp.carton_net_weight) / 1000.0 END AS estimated_kg_per_carton,
                (SELECT SUM(l.kg) FROM epr_packing_lines l WHERE l.stock_delivery_item_id = sdi.id) AS kg,
                (SELECT osp.slug FROM org_supplier_products osp WHERE osp.supplier_product_id = sp.id AND osp.organisation_id = sd.organisation_id LIMIT 1) AS org_supplier_product_slug
            FROM stock_delivery_items sdi
            JOIN stock_deliveries sd ON sd.id = sdi.stock_delivery_id
            JOIN supplier_products sp ON sp.id = sdi.supplier_product_id
            LEFT JOIN historic_supplier_products hsp ON hsp.id = sdi.historic_supplier_product_id
            LEFT JOIN agents a ON a.id = sd.agent_id
            CROSS JOIN LATERAL (
                SELECT COALESCE(sd.received_at, sd.checked_at, sd.placed_at, sd.date)::date AS date,
                    CASE WHEN sdi.state IN ('checked', 'placed') THEN sdi.unit_quantity_checked ELSE sdi.unit_quantity END AS units
            ) flow
            LEFT JOIN LATERAL (
                SELECT s.*
                FROM supplier_product_packing_specs s
                WHERE s.supplier_product_id = sdi.supplier_product_id AND s.status <> 'draft'
                ORDER BY (s.effective_from <= flow.date) DESC, CASE WHEN s.effective_from <= flow.date THEN -s.version ELSE s.version END
                LIMIT 1
            ) spec ON TRUE
            WHERE sd.organisation_id = ? AND flow.date BETWEEN ? AND ?
              AND sd.deleted_at IS NULL AND sdi.deleted_at IS NULL
              AND sd.state IN ('received', 'checked', 'booking_in', 'booked_in', 'placed')
              AND sdi.state NOT IN ('cancelled', 'not_received')
              AND flow.units > 0
            SQL,
            $bindings
        );

        $rows     = collect($rows);
        $withSpec = $rows->where('bucket', '!=', 'no_spec');
        $average  = $withSpec->sum(fn ($row) => (float)$row->cartons) > 0
            ? $withSpec->sum(fn ($row) => (float)$row->kg) / $withSpec->sum(fn ($row) => (float)$row->cartons)
            : null;
        $estimate = fn ($row) => $row->estimated_kg_per_carton !== null ? (float)$row->cartons * (float)$row->estimated_kg_per_carton : ($average === null ? null : (float)$row->cartons * $average);

        $buckets = collect(['verified', 'declared', 'no_spec'])->mapWithKeys(function (string $bucket) use ($rows, $estimate) {
            $inBucket = $rows->where('bucket', $bucket);

            return [$bucket => [
                'products'     => $inBucket->pluck('supplier_product_id')->unique()->count(),
                'cartons'      => round($inBucket->sum(fn ($row) => (float)$row->cartons), 1),
                'kg'           => round($inBucket->sum(fn ($row) => (float)$row->kg), 1),
                'estimated_kg' => $bucket === 'no_spec' ? round($inBucket->sum(fn ($row) => (float)$estimate($row)), 1) : null,
                'unknown'      => $bucket === 'no_spec' ? $inBucket->filter(fn ($row) => $estimate($row) === null)->pluck('supplier_product_id')->unique()->count() : null,
            ]];
        });

        $cartons = $rows->sum(fn ($row) => (float)$row->cartons);

        return [
            'buckets'       => $buckets->all(),
            'cartons'       => round($cartons, 1),
            'average_kg_per_carton' => $average === null ? null : round($average, 3),
            'coverage_pct'  => $cartons > 0 ? round(($buckets['verified']['cartons'] + $buckets['declared']['cartons']) / $cartons * 100, 1) : null,
            'to_declare'    => $rows->where('bucket', 'no_spec')
                ->groupBy('supplier_product_id')
                ->map(fn ($items) => [
                    'code'                      => $items->first()->code,
                    'name'                      => $items->first()->name,
                    'agent'                     => $items->first()->agent,
                    'cartons'                   => round($items->sum(fn ($row) => (float)$row->cartons), 1),
                    'estimated_kg'              => $estimate($items->first()) === null ? null : round($items->sum(fn ($row) => (float)$estimate($row)), 1),
                    'org_supplier_product_slug' => $items->first()->org_supplier_product_slug,
                ])
                ->sortByDesc('cartons')
                ->take(self::ROWS)
                ->values()
                ->all(),
        ];
    }

    /**
     * Each agent's products received by the organisation in the last twelve months and how many of them it has
     * declared, ordered by cartons: the backfill order.
     *
     * @return list<array<string, mixed>>
     */
    private function league(Organisation $organisation, ?User $user): array
    {
        $canSetCutOff = $user?->authTo(GroupPermissionsEnum::SUPPLY_CHAIN_EDIT->value) ?? false;

        return collect(DB::select(
            <<<'SQL'
            WITH products AS (
                SELECT sd.agent_id, sdi.supplier_product_id,
                    SUM(CASE WHEN sdi.state IN ('checked', 'placed') THEN sdi.unit_quantity_checked ELSE sdi.unit_quantity END / GREATEST(COALESCE(hsp.units_per_carton, sp.units_per_carton, 1), 1)) AS cartons
                FROM stock_delivery_items sdi
                JOIN stock_deliveries sd ON sd.id = sdi.stock_delivery_id
                JOIN supplier_products sp ON sp.id = sdi.supplier_product_id
                LEFT JOIN historic_supplier_products hsp ON hsp.id = sdi.historic_supplier_product_id
                WHERE sd.organisation_id = ? AND sd.agent_id IS NOT NULL
                  AND sd.deleted_at IS NULL AND sdi.deleted_at IS NULL
                  AND sd.state IN ('received', 'checked', 'booking_in', 'booked_in', 'placed')
                  AND sdi.state NOT IN ('cancelled', 'not_received')
                  AND COALESCE(sd.received_at, sd.date) >= ?
                GROUP BY sd.agent_id, sdi.supplier_product_id
            )
            SELECT a.id, a.code, a.name, a.settings ->> ? AS cut_off,
                COUNT(*) AS products, SUM(p.cartons) AS cartons,
                COUNT(*) FILTER (WHERE live.status IS NOT NULL) AS declared,
                COUNT(*) FILTER (WHERE live.status = 'verified') AS verified,
                COUNT(*) FILTER (WHERE live.status = 'returned') AS returned,
                COUNT(*) FILTER (WHERE live.needs_review) AS in_review,
                SUM(p.cartons) FILTER (WHERE live.status IS NOT NULL) AS cartons_declared
            FROM products p
            JOIN agents a ON a.id = p.agent_id
            LEFT JOIN LATERAL (
                SELECT s.status, s.needs_review
                FROM supplier_product_packing_specs s
                WHERE s.supplier_product_id = p.supplier_product_id AND s.status IN ('submitted', 'verified', 'returned')
                ORDER BY s.version DESC
                LIMIT 1
            ) live ON TRUE
            GROUP BY a.id, a.code, a.name, a.settings
            ORDER BY cartons DESC
            SQL,
            [$organisation->id, now()->subMonths(self::LEAGUE_MONTHS)->toDateString(), GetPurchaseOrderPackingSpecGaps::CUT_OFF_SETTING]
        ))->map(fn ($row) => [
            'id'                 => $row->id,
            'code'               => $row->code,
            'name'               => $row->name,
            'products'           => (int)$row->products,
            'cartons'            => round((float)$row->cartons),
            'declared'           => (int)$row->declared,
            'verified'           => (int)$row->verified,
            'returned'           => (int)$row->returned,
            'in_review'          => (int)$row->in_review,
            'coverage_pct'       => $row->cartons > 0 ? round((float)$row->cartons_declared / (float)$row->cartons * 100, 1) : null,
            'cut_off'            => $row->cut_off,
            'cut_off_route'      => $canSetCutOff ? ['name' => 'grp.models.agent.packing_spec_cut_off.update', 'parameters' => [$row->id]] : null,
        ])->all();
    }
}
