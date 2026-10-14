<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 02:10:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierAgreedTerm;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgreedTermsAuthorisation;
use App\Enums\Procurement\SupplierAgreedTerm\SupplierAgreedTermStateEnum;
use App\Models\Procurement\SupplierAgreedTerm;
use Lorisleiva\Actions\ActionRequest;

class WithdrawSupplierAgreedTerm extends OrgAction
{
    use WithAgreedTermsAuthorisation;

    private SupplierAgreedTerm $supplierAgreedTerm;

    public function handle(SupplierAgreedTerm $supplierAgreedTerm): SupplierAgreedTerm
    {
        if ($supplierAgreedTerm->state === SupplierAgreedTermStateEnum::ACTIVE) {
            $supplierAgreedTerm->update([
                'state'    => SupplierAgreedTermStateEnum::WITHDRAWN,
                'valid_to' => today(),
            ]);
        }

        return $supplierAgreedTerm;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $this->supplierAgreedTerm->agreed_by_user_id === $request->user()->id
            || $this->isProcurementSupervisor($request->user(), $this->group, $this->supplierAgreedTerm->organisation_id);
    }

    public function action(SupplierAgreedTerm $supplierAgreedTerm): SupplierAgreedTerm
    {
        $this->asAction = true;

        return $this->handle($supplierAgreedTerm);
    }

    public function withdraw(SupplierAgreedTerm $supplierAgreedTerm, ActionRequest $request): void
    {
        $this->supplierAgreedTerm = $supplierAgreedTerm;
        $this->initialisationFromGroup($supplierAgreedTerm->group, $request);
        $this->handle($supplierAgreedTerm);
    }
}
