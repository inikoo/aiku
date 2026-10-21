<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 12:15
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Goods\Packaging;

use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Rebuilds an organisation's inbound packing lines for a date range: every received stock delivery item of a supplier
 * product with a packing spec, times the cartons it came in (units and packs per carton of the supplier product
 * version it was ordered as), one line per packing material in kg. The spec is the
 * version effective on the day it arrived, else the first one declared. Imported material is split by what the
 * warehouse does with it (passed on, discarded, reused for dispatch) but is reported once, at import. Materials that
 * are not packaging for EPR (silica gel) are left out, and every line is non-household. Material passed on to the buyer
 * is flagged in_product_packaging when the SKO's trade unit already carries weighed packaging: the weighing of the
 * product as the buyer gets it includes it, so the returns leave it out rather than count it twice.
 */
class BuildEprPackingLines
{
    use AsAction;

    /**
     * Received stock delivery items of an organisation with the day they arrived, where they came from and the units
     * that arrived (counted once checked, else as ordered). origin.activity is imported, bought_domestic,
     * purchase_unknown_origin or packed_filled.
     */
    public const string DELIVERIES_SQL = <<<'SQL'
        FROM stock_delivery_items sdi
        JOIN stock_deliveries sd ON sd.id = sdi.stock_delivery_id
        JOIN organisations organisation ON organisation.id = sd.organisation_id
        JOIN supplier_products sp ON sp.id = sdi.supplier_product_id
        LEFT JOIN historic_supplier_products hsp ON hsp.id = sdi.historic_supplier_product_id
        LEFT JOIN suppliers supplier ON supplier.id = sd.supplier_id
        LEFT JOIN addresses supplier_address ON supplier_address.id = supplier.address_id
        LEFT JOIN agents agent ON agent.id = sd.agent_id
        LEFT JOIN organisations agent_organisation ON agent_organisation.id = agent.organisation_id
        LEFT JOIN organisations partner ON partner.id = sd.partner_id
        CROSS JOIN LATERAL (
            SELECT COALESCE(sd.received_at, sd.checked_at, sd.placed_at, sd.date)::date AS date,
                CASE sd.parent_type
                    WHEN 'OrgSupplier' THEN supplier_address.country_id
                    WHEN 'OrgAgent' THEN agent_organisation.country_id
                    WHEN 'OrgPartner' THEN partner.country_id
                    WHEN 'Production' THEN organisation.country_id
                END AS country_id,
                CASE WHEN sdi.state IN ('checked', 'placed') THEN sdi.unit_quantity_checked ELSE sdi.unit_quantity END AS units
        ) flow
        CROSS JOIN LATERAL (
            SELECT CASE
                WHEN sd.parent_type = 'Production' THEN 'packed_filled'
                WHEN flow.country_id IS NULL THEN 'purchase_unknown_origin'
                WHEN flow.country_id = organisation.country_id THEN 'bought_domestic'
                ELSE 'imported'
            END AS activity
        ) origin
        SQL;

    public const string RECEIVED_SQL = <<<'SQL'
        sd.deleted_at IS NULL AND sdi.deleted_at IS NULL
        AND sd.state IN ('received', 'checked', 'booking_in', 'booked_in', 'placed')
        AND sdi.state NOT IN ('cancelled', 'not_received')
        AND flow.units > 0
        SQL;

    /**
     * The packing spec a delivery item was packed with: the latest version effective on the day it arrived, else the
     * first one declared. Kept in step with SupplierProductPackingSpec::effectiveFor().
     */
    public const string SPEC_SQL = <<<'SQL'
        SELECT s.*
        FROM supplier_product_packing_specs s
        WHERE s.supplier_product_id = sdi.supplier_product_id AND s.status <> 'draft'
        ORDER BY (s.effective_from <= flow.date) DESC, CASE WHEN s.effective_from <= flow.date THEN -s.version ELSE s.version END
        LIMIT 1
        SQL;

    public const string UNITS_PER_CARTON_SQL = 'GREATEST(COALESCE(hsp.units_per_carton, sp.units_per_carton, 1), 1)';

    public function handle(Organisation $organisation, Carbon $from, Carbon $to): int
    {
        $bindings = ['organisation_id' => $organisation->id, 'from' => $from->toDateString(), 'to' => $to->toDateString()];

        return DB::transaction(function () use ($bindings) {
            DB::delete('DELETE FROM epr_packing_lines WHERE organisation_id = :organisation_id AND date BETWEEN :from AND :to', $bindings);

            return DB::affectingStatement($this->sql(), $bindings);
        });
    }

    private function sql(): string
    {
        $deliveries     = self::DELIVERIES_SQL;
        $received       = self::RECEIVED_SQL;
        $spec           = self::SPEC_SQL;
        $unitsPerCarton = self::UNITS_PER_CARTON_SQL;

        return <<<SQL
            INSERT INTO epr_packing_lines (group_id, organisation_id, date, activity, stock_delivery_id, stock_delivery_item_id, agent_id, supplier_product_id, packing_spec_id, packing_spec_line_id,
                packaging_component_id, counterparty_country_id, level, material_category, polymer, fate, spec_status, weight_is_measured, is_household, in_product_packaging, cartons, quantity, kg, created_at)
            SELECT sdi.group_id, sd.organisation_id, flow.date,
                CASE
                    WHEN origin.activity <> 'imported' THEN origin.activity
                    WHEN l.fate_at_our_warehouse = 'discarded' THEN 'imported_discarded'
                    WHEN l.fate_at_our_warehouse = 'reused_for_dispatch' THEN 'reused'
                    ELSE 'imported'
                END,
                sd.id, sdi.id, sd.agent_id, sdi.supplier_product_id, spec.id, l.id, l.packaging_component_id, flow.country_id, l.level, pc.material_category, pc.polymer,
                l.fate_at_our_warehouse, spec.status, l.weight_is_measured, FALSE,
                l.fate_at_our_warehouse = 'passed_on' AND EXISTS (
                    SELECT 1
                    FROM model_has_trade_units mhtu
                    JOIN trade_units tu ON tu.id = mhtu.trade_unit_id
                    JOIN packaging_families pf ON pf.id = tu.packaging_family_id AND NOT pf.is_product_itself
                    JOIN packaging_family_has_components fhc ON fhc.packaging_family_id = pf.id
                    JOIN packaging_components pc ON pc.id = fhc.packaging_component_id AND pc.weight_g > 0
                    WHERE mhtu.model_type = 'OrgStock' AND mhtu.model_id = sdi.org_stock_id
                ),
                carton.cartons, carton.cartons * per_carton.quantity, carton.cartons * per_carton.quantity * l.weight_g_each / 1000, NOW()
            {$deliveries}
            CROSS JOIN LATERAL (
                {$spec}
            ) spec
            CROSS JOIN LATERAL (
                SELECT {$unitsPerCarton} AS units_per_carton,
                    {$unitsPerCarton}::numeric / GREATEST(COALESCE(hsp.units_per_pack, sp.units_per_pack, 1), 1) AS packs_per_carton
            ) unit
            CROSS JOIN LATERAL (SELECT flow.units / unit.units_per_carton AS cartons) carton
            JOIN packing_spec_lines l ON l.packing_spec_id = spec.id AND l.is_epr_reportable AND l.weight_g_each > 0
            JOIN packaging_components pc ON pc.id = l.packaging_component_id
            CROSS JOIN LATERAL (
                SELECT CASE l.level
                    WHEN 'per_unit' THEN l.quantity * unit.units_per_carton
                    WHEN 'per_pack' THEN l.quantity * unit.packs_per_carton
                    ELSE l.quantity
                END AS quantity
            ) per_carton
            WHERE sd.organisation_id = :organisation_id
              AND {$received}
              AND flow.date BETWEEN :from AND :to
            SQL;
    }
}
