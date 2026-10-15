<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 02:10:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierAgreedTerm;

use App\Actions\Helpers\Media\SaveModelAttachment;
use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgreedTermsAuthorisation;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteVisibilityEnum;
use App\Enums\Procurement\SupplierAgreedTerm\SupplierAgreedTermStateEnum;
use App\Enums\Procurement\SupplierAgreedTerm\SupplierAgreedTermTypeEnum;
use App\Enums\Procurement\SupplierAgreedTerm\SupplierAgreedTermValueTypeEnum;
use App\Models\Procurement\SupplierAgreedTerm;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StoreSupplierAgreedTerm extends OrgAction
{
    use WithAgreedTermsAuthorisation;

    private Supplier|SupplierProduct|Agent $subject;

    public function handle(Supplier|SupplierProduct|Agent $subject, array $modelData): SupplierAgreedTerm
    {
        $attachments = Arr::pull($modelData, 'attachments', []);
        $superseded  = $this->termToSupersede($subject, $modelData);
        Arr::forget($modelData, 'supersedes_id');

        return DB::transaction(function () use ($subject, $modelData, $attachments, $superseded) {
            $term = SupplierAgreedTerm::create(array_merge($modelData, [
                'group_id'     => $subject->group_id,
                'subject_type' => $subject->getMorphClass(),
                'subject_id'   => $subject->id,
                'state'        => SupplierAgreedTermStateEnum::ACTIVE,
            ]));

            $superseded?->update([
                'state'            => SupplierAgreedTermStateEnum::SUPERSEDED,
                'superseded_by_id' => $term->id,
                'valid_to'         => $term->valid_from ?? today(),
            ]);

            foreach ($attachments as $file) {
                SaveModelAttachment::make()->action($term, [
                    'path'         => $file->getPathName(),
                    'originalName' => $file->getClientOriginalName(),
                    'extension'    => $file->getClientOriginalExtension(),
                    'scope'        => 'Evidence',
                    'caption'      => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                ]);
            }

            return $term;
        });
    }

    private function termToSupersede(Supplier|SupplierProduct|Agent $subject, array $modelData): ?SupplierAgreedTerm
    {
        $activeTerms = SupplierAgreedTerm::active()
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->id);

        if ($supersedesId = Arr::get($modelData, 'supersedes_id')) {
            return $activeTerms->find($supersedesId);
        }

        if ($modelData['term_type'] === SupplierAgreedTermTypeEnum::OTHER_CHARGE->value) {
            return null;
        }

        $organisationId = Arr::get($modelData, 'organisation_id');

        return $activeTerms
            ->where('term_type', $modelData['term_type'])
            ->when($organisationId, fn ($query) => $query->where('organisation_id', $organisationId), fn ($query) => $query->whereNull('organisation_id'))
            ->first();
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $this->canWriteProcurementNotes($request->user(), $this->group, $request->integer('organisation_id') ?: null);
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $supervisor = $this->asAction || $this->isProcurementSupervisor($request->user(), $this->group);

        if (!$supervisor || !$this->has('agreed_by_user_id')) {
            $this->set('agreed_by_user_id', $request->user()?->id ?? $this->get('agreed_by_user_id'));
        }
    }

    public function rules(): array
    {
        return [
            'organisation_id'     => ['sometimes', 'nullable', 'integer', Rule::exists('organisations', 'id')->where('group_id', $this->group->id)],
            'term_type'           => ['required', Rule::enum(SupplierAgreedTermTypeEnum::class)],
            'value_type'          => ['required', Rule::enum(SupplierAgreedTermValueTypeEnum::class)],
            'amount'              => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'currency_id'         => ['sometimes', 'nullable', 'integer', 'exists:currencies,id'],
            'value_text'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'tolerance'           => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'agreed_on'           => ['required', 'date', 'before_or_equal:today'],
            'agreed_by_user_id'   => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')->where('group_id', $this->group->id)],
            'agreed_with'         => ['sometimes', 'nullable', 'string', 'max:255'],
            'evidence_message_id' => ['sometimes', 'nullable', 'integer', Rule::exists('supplier_messages', 'id')->where('group_id', $this->group->id)],
            'attachments'         => ['sometimes', 'array'],
            'attachments.*'       => ['file', 'max:50000'],
            'valid_from'          => ['sometimes', 'nullable', 'date'],
            'visibility'          => ['sometimes', Rule::enum(ProcurementNoteVisibilityEnum::class)],
            'show_on_po'          => ['sometimes', 'boolean'],
            'supersedes_id'       => ['sometimes', 'nullable', 'integer'],
        ];
    }

    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        $termType  = SupplierAgreedTermTypeEnum::tryFrom((string) $this->get('term_type'));
        $valueType = SupplierAgreedTermValueTypeEnum::tryFrom((string) $this->get('value_type'));
        if (!$termType || !$valueType) {
            return;
        }

        $hasAmount = $this->get('amount') !== null && $this->get('amount') !== '';

        if ($valueType->hasAmount() && !$hasAmount) {
            $validator->errors()->add('amount', __('Enter the agreed amount'));
        }

        if ($hasAmount && $valueType->hasAmount() && $valueType !== SupplierAgreedTermValueTypeEnum::PERCENTAGE && !$this->get('currency_id')) {
            $validator->errors()->add('currency_id', __('Choose the currency of the amount'));
        }

        $needsText = $valueType === SupplierAgreedTermValueTypeEnum::TEXT
            || in_array($termType, [SupplierAgreedTermTypeEnum::INCOTERM, SupplierAgreedTermTypeEnum::OTHER_CHARGE]);
        if ($needsText && !trim((string) $this->get('value_text'))) {
            $validator->errors()->add('value_text', $termType === SupplierAgreedTermTypeEnum::OTHER_CHARGE ? __('Describe the charge') : __('Write what was agreed'));
        }

        if ($termType->isCharge() && $hasAmount && !$this->get('evidence_message_id') && !$this->get('attachments')) {
            $validator->errors()->add('evidence_message_id', __('A charge with an amount needs evidence: attach the email, chat or document'));
        }

        $superseded = $this->termToSupersede($this->subject, [
            'supersedes_id'   => $this->get('supersedes_id'),
            'term_type'       => $termType->value,
            'organisation_id' => $this->get('organisation_id'),
        ]);
        if ($this->get('supersedes_id') && !$superseded) {
            $validator->errors()->add('supersedes_id', __('The term to replace is no longer active'));
        }

        if ($superseded && !$this->asAction
            && $superseded->agreed_by_user_id !== $request->user()->id
            && !$this->isProcurementSupervisor($request->user(), $this->group)) {
            $validator->errors()->add('term_type', __('Only the procurement supervisor or :name can change this term', ['name' => $superseded->agreedBy?->contact_name ?? __('whoever agreed it')]));
        }
    }

    public function action(Supplier|SupplierProduct|Agent $subject, array $modelData): SupplierAgreedTerm
    {
        $this->asAction = true;
        $this->subject  = $subject;
        $this->initialisationFromGroup($subject->group, $modelData);

        return $this->handle($subject, $this->validatedData);
    }

    public function inSupplier(Supplier $supplier, ActionRequest $request): void
    {
        $this->storeFromRequest($supplier, $request);
    }

    public function inSupplierProduct(SupplierProduct $supplierProduct, ActionRequest $request): void
    {
        $this->storeFromRequest($supplierProduct, $request);
    }

    public function inAgent(Agent $agent, ActionRequest $request): void
    {
        $this->storeFromRequest($agent, $request);
    }

    private function storeFromRequest(Supplier|SupplierProduct|Agent $subject, ActionRequest $request): void
    {
        $this->subject = $subject;
        $this->group   = $subject->group;
        $this->initialisationFromGroup($subject->group, $request);
        $this->handle($subject, $this->validatedData);
    }
}
