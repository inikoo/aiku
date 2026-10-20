<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Reports;

use App\Actions\OrgAction;
use App\Enums\Goods\Packaging\EprSchemeEnum;
use App\Models\Goods\EprSnapshot;
use App\Models\Goods\EprSnapshotLine;
use App\Models\Helpers\Country;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A frozen EPR return as a csv: the return as it was submitted (?part=return), or the SKO lines behind it with the
 * weights and evidence used.
 */
class ExportEprSnapshot extends OrgAction
{
    public const array LINE_COLUMNS = [
        'activity', 'country', 'channel', 'sko_code', 'org_stock_id', 'trade_unit_id', 'packaging_family_id', 'packaging_family_code', 'brand_ownership', 'end_use', 'weights_source',
        'is_product_itself', 'packaging_component_id', 'packaging_level', 'material_category', 'polymer', 'weight_g', 'weight_is_measured', 'quantity_per_unit', 'ram_rating',
        'ram_assessed_at', 'ram_evidence_ref', 'units', 'kg',
    ];

    /**
     * @return array{list<string>, iterable<list<mixed>>}
     */
    public function handle(Organisation $organisation, EprSnapshot $snapshot, bool $returnOnly): array
    {
        if ($returnOnly) {
            return $snapshot->scheme === EprSchemeEnum::UK
                ? [ExportUkPackagingReturn::COLUMNS, ExportUkPackagingReturn::make()->handle($organisation, $snapshot->data)]
                : [ExportEuPackagingReturn::COLUMNS, ExportEuPackagingReturn::make()->handle($snapshot->data)];
        }

        $countries = Country::pluck('code', 'id');

        return [self::LINE_COLUMNS, $snapshot->lines()->orderBy('id')->lazy()->map(fn (EprSnapshotLine $line) => [
            $line->activity, $countries[$line->counterparty_country_id] ?? null, $line->channel, $line->org_stock_code, $line->org_stock_id, $line->trade_unit_id,
            $line->packaging_family_id, $line->packaging_family_code, $line->brand_ownership, $line->end_use, $line->family_source, $line->is_product_itself === null ? null : (int)$line->is_product_itself,
            $line->packaging_component_id, $line->packaging_level, $line->material_category, $line->polymer, $line->weight_g, $line->weight_is_measured === null ? null : (int)$line->weight_is_measured,
            $line->quantity_per_unit, $line->ram_rating, $line->ram_assessed_at?->toDateString(), $line->ram_evidence_ref, $line->units, $line->kg,
        ])];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo('org-reports.'.$this->organisation->id);
    }

    public function asController(Organisation $organisation, EprSnapshot $eprSnapshot, ActionRequest $request): StreamedResponse
    {
        $this->initialisation($organisation, $request);
        abort_unless($eprSnapshot->organisation_id === $organisation->id, 404);

        $returnOnly      = $request->input('part') === 'return';
        [$columns, $rows] = $this->handle($organisation, $eprSnapshot, $returnOnly);
        $name            = "packaging-{$eprSnapshot->scheme->value}-".($returnOnly ? 'return' : 'audit')."-{$organisation->slug}-{$eprSnapshot->date_from->toDateString()}-{$eprSnapshot->date_to->toDateString()}-$eprSnapshot->id.csv";

        return response()->streamDownload(function () use ($columns, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $name, ['Content-Type' => 'text/csv']);
    }
}
