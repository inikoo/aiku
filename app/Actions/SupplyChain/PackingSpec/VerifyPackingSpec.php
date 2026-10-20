<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 09:55
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\SupplyChain\PackingSpec;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Enums\SupplyChain\PackingSpec\PackingSpecStatusEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\SupplyChain\SupplierProductPackingSpec;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

/**
 * Marks a declared packing spec as verified: compliance cleared an "Other" material, or goods in weighed the materials
 * and a carton matched.
 */
class VerifyPackingSpec extends OrgAction
{
    use WithProcurementEditAuthorisation;

    public function handle(SupplierProductPackingSpec $packingSpec, ?User $user = null): SupplierProductPackingSpec
    {
        if (!in_array($packingSpec->status, [PackingSpecStatusEnum::SUBMITTED, PackingSpecStatusEnum::RETURNED])) {
            return $packingSpec;
        }

        $packingSpec->update([
            'status'              => PackingSpecStatusEnum::VERIFIED,
            'needs_review'        => false,
            'verified_by_user_id' => $user?->id,
            'verified_at'         => now(),
        ]);

        return $packingSpec;
    }

    public function asController(Organisation $organisation, SupplierProductPackingSpec $packingSpec, ActionRequest $request): RedirectResponse
    {
        abort_unless($packingSpec->group_id === $organisation->group_id, 404);
        abort_if($organisation->type === OrganisationTypeEnum::AGENT, 403);
        $this->initialisation($organisation, $request);
        $this->handle($packingSpec, $request->user());

        return redirect()->back();
    }
}
