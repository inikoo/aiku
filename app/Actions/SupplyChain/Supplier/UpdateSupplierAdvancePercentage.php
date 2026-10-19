<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier;

use App\Actions\OrgAction;
use App\Models\SupplyChain\Supplier;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

/**
 * The agent sets how much of an order with this supplier it asks the client for in advance, even when the
 * supplier itself asks for no deposit. Empty goes back to the default 30%.
 */
class UpdateSupplierAdvancePercentage extends OrgAction
{
    private Supplier $supplier;

    public function handle(Supplier $supplier, array $modelData): Supplier
    {
        $supplier->update(['advance_percentage' => $modelData['advance_percentage'] ?? null]);

        return $supplier;
    }

    public function rules(): array
    {
        return [
            'advance_percentage' => ['present', 'nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        $agentOrganisationId = $this->supplier->agent?->organisation_id;

        return $agentOrganisationId !== null && $request->user()->authTo("procurement.$agentOrganisationId.edit");
    }

    public function asController(Supplier $supplier, ActionRequest $request): Supplier
    {
        $this->supplier = $supplier;
        abort_unless($supplier->agent, 404);
        $this->initialisation($supplier->agent->organisation, $request);

        return $this->handle($supplier, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
