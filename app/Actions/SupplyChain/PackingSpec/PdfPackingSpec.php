<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 10:20
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\SupplyChain\PackingSpec;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Enums\SupplyChain\PackingSpec\PackingSpecLevelEnum;
use App\Models\SupplyChain\PackingSpecLine;
use App\Models\SupplyChain\SupplierProductPackingSpec;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Response;
use Lorisleiva\Actions\ActionRequest;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

/**
 * The packing instruction the agent prints for its packers: what goes on each unit, on each pack and in each carton,
 * and how many, with the material names in English, Chinese and Indonesian.
 */
class PdfPackingSpec extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementAuthorisation;

    public function handle(SupplierProductPackingSpec $packingSpec): string
    {
        $packingSpec->loadMissing(['supplierProduct.supplier', 'lines.packagingComponent', 'agent']);
        $photo = StorePackingSpec::make()->photoOf($packingSpec);

        $blocks = collect(PackingSpecLevelEnum::cases())->mapWithKeys(fn (PackingSpecLevelEnum $level) => [
            $level->value => [
                'label' => PackingSpecLevelEnum::labels()[$level->value],
                'lines' => $packingSpec->lines->filter(fn (PackingSpecLine $line) => $line->level === $level)->values(),
            ],
        ]);

        return PDF::loadView('procurement.templates.pdf.packing-spec', [
            'spec'    => $packingSpec,
            'product' => $packingSpec->supplierProduct,
            'blocks'  => $blocks,
            'photo'   => $photo?->getPath() && file_exists($photo->getPath()) ? $photo->getPath() : null,
        ], [], ['autoScriptToLang' => true, 'autoLangToFont' => true])->output();
    }

    public function asController(Organisation $organisation, SupplierProductPackingSpec $packingSpec, ActionRequest $request): Response
    {
        $this->initialisation($organisation, $request);
        $agent = $this->getOrganisationAgent($organisation);
        abort_unless($packingSpec->group_id === $organisation->group_id, 404);
        abort_if($agent && $packingSpec->agent_id !== $agent->id && $packingSpec->supplierProduct->agent_id !== $agent->id, 404);

        $filename = preg_replace('/[^A-Za-z0-9._-]/', '-', 'packing-'.$packingSpec->supplierProduct->code.'-v'.$packingSpec->version).'.pdf';

        return response($this->handle($packingSpec), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
    }
}
