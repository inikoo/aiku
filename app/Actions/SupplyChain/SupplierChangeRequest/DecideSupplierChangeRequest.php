<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierChangeRequest;

use App\Actions\OrgAction;
use App\Actions\SupplyChain\Supplier\DeactivateSupplier;
use App\Actions\SupplyChain\Supplier\MergeSuppliers;
use App\Enums\SupplyChain\SupplierChangeRequest\SupplierChangeRequestStateEnum;
use App\Enums\SupplyChain\SupplierChangeRequest\SupplierChangeRequestTypeEnum;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\SupplyChain\SupplierChangeRequest;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * The directors approve a suggestion, which deactivates or merges the supplier, or keep the supplier as it
 * is. Who decided, when and why stays on the suggestion.
 */
class DecideSupplierChangeRequest extends OrgAction
{
    private SupplierChangeRequest $supplierChangeRequest;

    public static function canDecide(User $user): bool
    {
        return $user->hasRole(RolesEnum::GROUP_ADMIN->value);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $this->asAction || self::canDecide($request->user());
    }

    public function handle(SupplierChangeRequest $supplierChangeRequest, array $modelData): SupplierChangeRequest
    {
        $approve = (bool) $modelData['approve'];

        if ($approve) {
            match ($supplierChangeRequest->type) {
                SupplierChangeRequestTypeEnum::DEACTIVATE => DeactivateSupplier::run($supplierChangeRequest->supplier),
                SupplierChangeRequestTypeEnum::MERGE      => MergeSuppliers::run($supplierChangeRequest->supplier, $supplierChangeRequest->targetSupplier),
            };
        }

        $supplierChangeRequest->update([
            'state'              => $approve ? SupplierChangeRequestStateEnum::APPROVED : SupplierChangeRequestStateEnum::REJECTED,
            'decided_by_user_id' => Arr::get($modelData, 'decided_by_user_id'),
            'decided_at'         => now(),
            'decision_note'      => Arr::get($modelData, 'decision_note'),
        ]);

        return $supplierChangeRequest;
    }

    public function rules(): array
    {
        return [
            'approve'            => ['required', 'boolean'],
            'decision_note'      => ['required_if:approve,false', 'nullable', 'string', 'max:2000'],
            'decided_by_user_id' => ['sometimes', 'nullable', 'integer'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if ($this->supplierChangeRequest->state !== SupplierChangeRequestStateEnum::PENDING) {
            $validator->errors()->add('approve', __('This suggestion was already decided'));
        }
    }

    public function asController(SupplierChangeRequest $supplierChangeRequest, ActionRequest $request): SupplierChangeRequest
    {
        $this->supplierChangeRequest = $supplierChangeRequest;
        $this->initialisationFromGroup($supplierChangeRequest->group, $request);

        return $this->handle($supplierChangeRequest, [...Arr::except($this->validatedData, 'decided_by_user_id'), 'decided_by_user_id' => $request->user()->id]);
    }

    public function action(SupplierChangeRequest $supplierChangeRequest, array $modelData): SupplierChangeRequest
    {
        $this->asAction              = true;
        $this->supplierChangeRequest = $supplierChangeRequest;
        $this->initialisationFromGroup($supplierChangeRequest->group, $modelData);

        return $this->handle($supplierChangeRequest, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
