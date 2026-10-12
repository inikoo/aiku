<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 07:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierPayment;

use App\Actions\OrgAction;
use App\Models\SupplyChain\SupplierPayment;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

class DeleteSupplierPayment extends OrgAction
{
    public function handle(SupplierPayment $supplierPayment): SupplierPayment
    {
        $supplierPayment->delete();

        return $supplierPayment;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo(["procurement.{$this->organisation->id}.edit", "accounting.{$this->organisation->id}.edit"]);
    }

    public function asController(SupplierPayment $supplierPayment, ActionRequest $request): SupplierPayment
    {
        $this->initialisation($supplierPayment->organisation, $request);

        return $this->handle($supplierPayment);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
