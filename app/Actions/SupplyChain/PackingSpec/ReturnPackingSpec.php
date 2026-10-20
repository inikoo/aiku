<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 09:55
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\SupplyChain\PackingSpec;

use App\Actions\OrgAction;
use App\Actions\Procurement\ProcurementNote\StoreProcurementNote;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteCategoryEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteSeverityEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteVisibilityEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecStatusEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\SupplyChain\SupplierProductPackingSpec;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Sends a packing spec back to the agent to correct: the goods stay where they are, only the declaration is wrong.
 * The spec stays live, so deliveries are still reported with it until the agent saves a new version.
 */
class ReturnPackingSpec extends OrgAction
{
    use WithProcurementEditAuthorisation;

    private SupplierProductPackingSpec $packingSpec;

    public function handle(SupplierProductPackingSpec $packingSpec, string $reason, ?User $user = null): SupplierProductPackingSpec
    {
        $packingSpec->update([
            'status'              => PackingSpecStatusEnum::RETURNED,
            'return_reason'       => $reason,
            'returned_at'         => now(),
            'returned_by_user_id' => $user?->id,
        ]);

        $supplierProduct = $packingSpec->supplierProduct;
        rescue(fn () => StoreProcurementNote::make()->action($supplierProduct->supplier ?? $supplierProduct, [
            'title'          => __('Packing spec v:version for :code returned to the agent', ['version' => $packingSpec->version, 'code' => $supplierProduct->code]),
            'body'           => $reason,
            'category'       => ProcurementNoteCategoryEnum::PACKAGING->value,
            'severity'       => ProcurementNoteSeverityEnum::WARNING->value,
            'visibility'     => ProcurementNoteVisibilityEnum::AGENT->value,
            'author_user_id' => $user?->id,
        ], strict: false));

        return $packingSpec;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:2000'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!in_array($this->packingSpec->status, [PackingSpecStatusEnum::SUBMITTED, PackingSpecStatusEnum::VERIFIED])) {
            $validator->errors()->add('reason', __('Only a declared or verified packing spec can be returned'));
        }
    }

    public function asController(Organisation $organisation, SupplierProductPackingSpec $packingSpec, ActionRequest $request): RedirectResponse
    {
        abort_unless($packingSpec->group_id === $organisation->group_id, 404);
        abort_if($organisation->type === OrganisationTypeEnum::AGENT, 403);
        $this->packingSpec = $packingSpec;
        $this->initialisation($organisation, $request);
        $this->handle($packingSpec, $this->validatedData['reason'], $request->user());

        return redirect()->back();
    }
}
