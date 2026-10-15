<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierChangeRequest;

use App\Actions\OrgAction;
use App\Enums\SupplyChain\SupplierChangeRequest\SupplierChangeRequestStateEnum;
use App\Enums\SupplyChain\SupplierChangeRequest\SupplierChangeRequestTypeEnum;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierChangeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * An agent, or supply chain staff, suggests deactivating a supplier or merging it into another record of the
 * same agent. Nothing changes until the directors approve it.
 */
class StoreSupplierChangeRequest extends OrgAction
{
    private Supplier $supplier;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction || $request->user()->authTo('supply-chain.edit')) {
            return true;
        }

        return $this->supplier->agent && $request->user()->authTo("procurement.{$this->supplier->agent->organisation_id}.edit");
    }

    public function handle(Supplier $supplier, array $modelData): SupplierChangeRequest
    {
        return SupplierChangeRequest::create([
            'group_id'             => $supplier->group_id,
            'agent_id'             => $supplier->agent_id,
            'supplier_id'          => $supplier->id,
            'type'                 => $modelData['type'],
            'state'                => SupplierChangeRequestStateEnum::PENDING,
            'target_supplier_id'   => Arr::get($modelData, 'target_supplier_id'),
            'reason'               => $modelData['reason'],
            'requested_by_user_id' => Arr::get($modelData, 'requested_by_user_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'type'                 => ['required', Rule::enum(SupplierChangeRequestTypeEnum::class)],
            'reason'               => ['required', 'string', 'max:2000'],
            'target_supplier_id'   => [
                Rule::requiredIf(fn () => $this->get('type') === SupplierChangeRequestTypeEnum::MERGE->value),
                'nullable',
                'integer',
                Rule::exists('suppliers', 'id')->where('agent_id', $this->supplier->agent_id)->where('status', true)->whereNull('deleted_at'),
                Rule::notIn([$this->supplier->id]),
            ],
            'requested_by_user_id' => ['sometimes', 'nullable', 'integer'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->supplier->status) {
            $validator->errors()->add('type', __('This supplier is not active'));
        }

        if (SupplierChangeRequest::where('supplier_id', $this->supplier->id)->where('state', SupplierChangeRequestStateEnum::PENDING)->exists()) {
            $validator->errors()->add('type', __('This supplier already has a suggestion waiting for the directors'));
        }
    }

    public function asController(Supplier $supplier, ActionRequest $request): SupplierChangeRequest
    {
        $this->supplier = $supplier;
        $this->initialisationFromGroup($supplier->group, $request);

        return $this->handle($supplier, [...Arr::except($this->validatedData, 'requested_by_user_id'), 'requested_by_user_id' => $request->user()->id]);
    }

    public function action(Supplier $supplier, array $modelData): SupplierChangeRequest
    {
        $this->asAction = true;
        $this->supplier = $supplier;
        $this->initialisationFromGroup($supplier->group, $modelData);

        return $this->handle($supplier, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
