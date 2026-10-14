<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 05:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierAgreedTerm;

use App\Actions\OrgAction;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\ProcurementNote;
use App\Models\Procurement\SupplierAgreedTerm;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Symfony\Component\HttpFoundation\Response;

class PdfSupplierAgreement extends OrgAction
{
    private bool $inGroup = false;

    /**
     * The handover sheet of a supplier: every term in force (the supplier's, its agent's and the
     * ones agreed for single products) and every note still open, for whoever takes the supplier over.
     */
    public function handle(Supplier $supplier, ?Organisation $organisation = null): string
    {

        $productTerms = SupplierAgreedTerm::active()
            ->forOrganisation($organisation)
            ->where('subject_type', 'SupplierProduct')
            ->whereIn('subject_id', $supplier->supplierProducts()->select('supplier_products.id'))
            ->with(['currency', 'agreedBy', 'subject'])
            ->get()
            ->sortBy(fn (SupplierAgreedTerm $term) => [$term->subject->code, $term->term_type->value])
            ->each(fn (SupplierAgreedTerm $term) => $term->setAttribute('product_code', $term->subject->code));

        $notes = ProcurementNote::active()
            ->forOrganisation($organisation)
            ->where(fn (Builder $query) => $query
                ->where(fn (Builder $query) => $query->where('subject_type', 'Supplier')->where('subject_id', $supplier->id))
                ->orWhere(fn (Builder $query) => $query->where('subject_type', 'SupplierProduct')->whereIn('subject_id', $supplier->supplierProducts()->select('supplier_products.id'))))
            ->with(['author', 'subject', 'purchaseOrder', 'incidentCurrency'])
            ->orderByDesc('is_pinned')
            ->orderBy('category')
            ->latest('id')
            ->get();

        return PDF::loadView('procurement.templates.pdf.supplier-agreement', [
            'supplier'     => $supplier,
            'organisation' => $organisation,
            'terms'        => GetEffectiveSupplierAgreedTerms::run($supplier, $organisation)->concat($productTerms),
            'notes'        => $notes,
        ])->output();
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $this->inGroup
            ? $request->user()->authTo('supply-chain.view')
            : $request->user()->authTo("procurement.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, OrgSupplier $orgSupplier, ActionRequest $request): Response
    {
        abort_unless($orgSupplier->organisation_id === $organisation->id && $organisation->type !== OrganisationTypeEnum::AGENT, 404);
        $this->initialisation($organisation, $request);

        return $this->pdfResponse($orgSupplier->supplier, $organisation);
    }

    public function inGroup(Supplier $supplier, ActionRequest $request): Response
    {
        $this->inGroup = true;
        $this->initialisationFromGroup($supplier->group, $request);

        return $this->pdfResponse($supplier, null);
    }

    private function pdfResponse(Supplier $supplier, ?Organisation $organisation): Response
    {
        return response($this->handle($supplier, $organisation), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="agreement-'.Str::slug($supplier->code).'.pdf"');
    }
}
