<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\SupplyChain\Supplier\UpdateSupplier;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

/**
 * An agent fills in the contacts of one of its suppliers from its products list.
 */
class UpdateAgentSupplierContacts extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementEditAuthorisation;

    public function handle(Supplier $supplier, array $modelData): Supplier
    {
        return UpdateSupplier::make()->action($supplier, $modelData);
    }

    public function rules(): array
    {
        return [
            'contact_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'wechat'       => ['sometimes', 'nullable', 'string', 'max:255'],
            'whatsapp'     => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function asController(Organisation $organisation, Supplier $supplier, ActionRequest $request): Supplier
    {
        $this->initialisation($organisation, $request);
        abort_unless($this->organisationAgentSupplies($supplier), 404);

        return $this->handle($supplier, $this->validatedData);
    }
}
