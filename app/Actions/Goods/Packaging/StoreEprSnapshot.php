<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Packaging;

use App\Actions\OrgAction;
use App\Actions\Reports\GetEuPackagingReturn;
use App\Actions\Reports\GetUkPackagingReturn;
use App\Enums\Goods\Packaging\EprSchemeEnum;
use App\Models\Goods\EprSnapshot;
use App\Models\Helpers\Country;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

/**
 * Freezes a packaging EPR return for audit: the return as produced now, and every SKO that moved in the period for that
 * return with the packaging family, component weights, polymer and RAM evidence it carried, so the figures submitted
 * can be shown seven years later after the packaging data has changed.
 */
class StoreEprSnapshot extends OrgAction
{
    public function handle(Organisation $organisation, array $modelData): EprSnapshot
    {
        $scheme = EprSchemeEnum::from($modelData['scheme']);
        $from   = Carbon::parse($modelData['date_from'])->startOfDay();
        $to     = Carbon::parse($modelData['date_to'])->startOfDay();

        $ownBrandImports = (bool)($modelData['own_brand_imports'] ?? false);
        $return          = $scheme === EprSchemeEnum::UK
            ? [...GetUkPackagingReturn::run($organisation, $from, $to, $ownBrandImports), 'own_brand_imports' => $ownBrandImports]
            : GetEuPackagingReturn::run($organisation, $from, $to, $scheme);

        return DB::transaction(function () use ($organisation, $modelData, $scheme, $from, $to, $return) {
            $snapshot = EprSnapshot::create([
                'group_id'        => $organisation->group_id,
                'organisation_id' => $organisation->id,
                'scheme'          => $scheme,
                'date_from'       => $from->toDateString(),
                'date_to'         => $to->toDateString(),
                'kg'              => $return['kg'],
                'data'            => $return,
                'notes'           => $modelData['notes'] ?? null,
                'user_id'         => $modelData['user_id'] ?? null,
            ]);

            DB::statement(
                $this->linesSql($this->activitiesSql($organisation, $scheme)),
                [$snapshot->id, $organisation->id, $from->toDateString(), $to->toDateString()]
            );

            return $snapshot;
        });
    }

    private function activitiesSql(Organisation $organisation, EprSchemeEnum $scheme): string
    {
        if ($scheme === EprSchemeEnum::UK) {
            return "l.activity IN ('sold_domestic', 'imported', 'bought_domestic', 'packed_filled', 'shipment_packaging')";
        }

        $countryId = (int)Country::where('code', $scheme->countryCode())->value('id');
        if ($countryId === $organisation->country_id) {
            return "l.activity IN ('sold_domestic', 'shipment_packaging')";
        }

        return "(l.activity = 'shipment_packaging' OR (l.activity = 'exported' AND l.counterparty_country_id = $countryId))";
    }

    private function linesSql(string $activities): string
    {
        $consumer = GetEuPackagingReturn::CONSUMER_CHANNEL_SQL;

        return <<<SQL
            INSERT INTO epr_snapshot_lines (epr_snapshot_id, activity, counterparty_country_id, channel, org_stock_id, org_stock_code, trade_unit_id,
                packaging_family_id, packaging_family_code, brand_ownership, end_use, family_source, is_product_itself, packaging_component_id, packaging_level,
                material_category, polymer, weight_g, weight_is_measured, quantity_per_unit, ram_rating, ram_assessed_at, ram_evidence_ref, units, kg)
            SELECT ?, l.activity, l.counterparty_country_id,
                CASE WHEN l.activity IN ('sold_domestic', 'exported') THEN CASE WHEN $consumer THEN 'consumer' ELSE 'trade' END END,
                l.org_stock_id, os.code, l.trade_unit_id, pf.id, pf.code, pf.brand_ownership, pf.end_use, pf.source, pf.is_product_itself,
                pc.id, pc.packaging_level, pc.material_category, pc.polymer, pc.weight_g, pc.weight_is_measured, fhc.quantity_per_unit,
                pc.ram_rating, pc.ram_assessed_at, pc.ram_evidence_ref,
                SUM(l.quantity), COALESCE(SUM(l.quantity * pc.weight_g * fhc.quantity_per_unit) / 1000, 0)
            FROM epr_flow_lines l
            LEFT JOIN org_stocks os ON os.id = l.org_stock_id
            LEFT JOIN trade_units tu ON tu.id = l.trade_unit_id
            LEFT JOIN packaging_families pf ON pf.id = tu.packaging_family_id
            LEFT JOIN packaging_family_has_components fhc ON fhc.packaging_family_id = pf.id
            LEFT JOIN packaging_components pc ON pc.id = fhc.packaging_component_id
            WHERE l.organisation_id = ? AND l.date BETWEEN ? AND ? AND $activities
            GROUP BY 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23
            SQL;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo('org-reports.'.$this->organisation->id);
    }

    public function rules(): array
    {
        return [
            'scheme'            => ['required', Rule::enum(EprSchemeEnum::class)],
            'date_from'         => ['required', 'date'],
            'date_to'           => ['required', 'date', 'after_or_equal:date_from'],
            'own_brand_imports' => ['sometimes', 'boolean'],
            'notes'             => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function asController(Organisation $organisation, ActionRequest $request): EprSnapshot
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, [...$this->validatedData, 'user_id' => $request->user()->id]);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
