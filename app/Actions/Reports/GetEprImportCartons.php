<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 14:00
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Reports;

use App\Actions\Goods\Packaging\BuildEprPackingLines;
use App\Actions\OrgAction;
use App\Enums\SupplyChain\PackingSpec\PackingSpecRoleEnum;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;

/**
 * The import cartons of a period worked out from the agents' packing specs, to fill the "Import cartons" line typed into
 * the UK return: the kg of master cartons declared for what arrived, and how many imported products came without a
 * packing spec. Those are not estimated, because units per carton is too often missing or 1 to count their cartons;
 * the team adds them from the container count. Master cartons are kept out of the automatic UK return so the typed
 * line is the only place they count.
 */
class GetEprImportCartons extends OrgAction
{
    public const array IMPORTED = ['imported', 'imported_discarded', 'reused'];

    /**
     * @return array{materials: array<string, float>, cartons_with_spec: float, products_without_spec: int, kg: float}
     */
    public function handle(Organisation $organisation, Carbon $from, Carbon $to): array
    {
        $bindings = [$organisation->id, $from->toDateString(), $to->toDateString()];
        $imported = "'".implode("', '", self::IMPORTED)."'";
        $role     = PackingSpecRoleEnum::MASTER_CARTON->value;

        $declared = collect(DB::select(
            <<<SQL
            SELECT l.material_category, SUM(l.kg) AS kg
            FROM epr_packing_lines l
            JOIN packing_spec_lines psl ON psl.id = l.packing_spec_line_id AND psl.role = '$role'
            WHERE l.organisation_id = ? AND l.date BETWEEN ? AND ? AND l.activity IN ($imported)
            GROUP BY l.material_category
            SQL,
            $bindings
        ))->mapWithKeys(fn ($row) => [$row->material_category => (float)$row->kg]);

        $cartonsWithSpec = (float)DB::selectOne(
            <<<SQL
            SELECT COALESCE(SUM(cartons), 0) AS cartons
            FROM (
                SELECT l.stock_delivery_item_id, MAX(l.cartons) AS cartons
                FROM epr_packing_lines l
                JOIN packing_spec_lines psl ON psl.id = l.packing_spec_line_id AND psl.role = '$role'
                WHERE l.organisation_id = ? AND l.date BETWEEN ? AND ? AND l.activity IN ($imported)
                GROUP BY l.stock_delivery_item_id
            ) items
            SQL,
            $bindings
        )->cartons;

        $deliveries          = BuildEprPackingLines::DELIVERIES_SQL;
        $received            = BuildEprPackingLines::RECEIVED_SQL;
        $productsWithoutSpec = (int)DB::selectOne(
            <<<SQL
            SELECT COUNT(DISTINCT sdi.supplier_product_id) AS products
            {$deliveries}
            WHERE sd.organisation_id = ? AND flow.date BETWEEN ? AND ?
              AND {$received}
              AND origin.activity = 'imported'
              AND NOT EXISTS (SELECT 1 FROM supplier_product_packing_specs s WHERE s.supplier_product_id = sdi.supplier_product_id AND s.status <> 'draft')
            SQL,
            $bindings
        )->products;

        return [
            'materials'             => $declared->map(fn (float $kg) => round($kg, 1))->all(),
            'cartons_with_spec'     => round($cartonsWithSpec, 1),
            'products_without_spec' => $productsWithoutSpec,
            'kg'                    => round((float)$declared->sum(), 1),
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo('org-reports.'.$this->organisation->id);
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ];
    }

    public function asController(Organisation $organisation, ActionRequest $request): array
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, Carbon::parse($this->validatedData['from'])->startOfDay(), Carbon::parse($this->validatedData['to'])->startOfDay());
    }

    public function jsonResponse(array $result): JsonResponse
    {
        return response()->json($result);
    }
}
