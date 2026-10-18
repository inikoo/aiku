<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 09 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\AgentPayment;

use App\Actions\Helpers\Media\SaveModelAttachment;
use App\Actions\OrgAction;
use App\Actions\Procurement\SupplierAgreedTerm\CheckAgainstAgreedTerms;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderAttachmentScopeEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderPaymentMethodEnum;
use App\Models\SysAdmin\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use App\Models\GoodsIn\StockDelivery;
use App\Models\SupplyChain\AgentPayment;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

/**
 * Records a payment one of our organisations made to an agent for a container without a deposit request. Only the
 * paying organisation records it: the agent sees its payments but can not add or remove them.
 */
class StoreAgentPayment extends OrgAction
{
    private StockDelivery $stockDelivery;

    /**
     * @param  array{date: string, amount: float|string, method?: string|null, reference?: string|null, notes?: string|null, bank_statement?: UploadedFile|null}  $modelData
     */
    public function handle(StockDelivery $stockDelivery, array $modelData): AgentPayment
    {
        $file  = Arr::get($modelData, 'bank_statement');
        $media = $file ? SaveModelAttachment::make()->action($stockDelivery, [
            'path'         => $file->getPathName(),
            'originalName' => $file->getClientOriginalName(),
            'extension'    => $file->getClientOriginalExtension(),
            'scope'        => PurchaseOrderAttachmentScopeEnum::BANK_STATEMENT->value,
            'caption'      => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
        ]) : null;

        return $stockDelivery->agentPayments()->create(array_filter([
            'method'   => Arr::get($modelData, 'method'),
            'media_id' => $media?->id,
        ]) + [
            'group_id'        => $stockDelivery->group_id,
            'agent_id'        => $stockDelivery->agent_id,
            'organisation_id' => $stockDelivery->organisation_id,
            'currency_id'     => $stockDelivery->currency_id,
            'date'            => $modelData['date'],
            'amount'          => round((float) $modelData['amount'], 2),
            'reference'       => $modelData['reference'] ?? null,
            'notes'           => $modelData['notes'] ?? null,
            'created_by'      => $this->asAction ? null : request()->user()?->id,
        ]);
    }

    /**
     * The organisation's purchaser or accountant, or the staff of the agent the delivery comes through.
     */
    public static function canRecord(StockDelivery $stockDelivery, User $user): bool
    {
        return $user->authTo(array_filter([
            "procurement.$stockDelivery->organisation_id.edit",
            "accounting.$stockDelivery->organisation_id.edit",
            ($agentOrganisationId = $stockDelivery->agent?->organisation_id) ? "procurement.$agentOrganisationId.edit" : null,
        ]));
    }

    public function rules(): array
    {
        return [
            'date'           => ['required', 'date'],
            'amount'         => ['required', 'numeric', 'gt:0', 'max:999999999999'],
            'method'         => ['required', Rule::enum(PurchaseOrderPaymentMethodEnum::class)],
            'reference'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes'          => ['sometimes', 'nullable', 'string', 'max:2000'],
            'bank_statement' => ['required_unless:method,cash', 'nullable', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,webp,heic'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->asAction && CheckAgainstAgreedTerms::hasUnaccepted(CheckAgainstAgreedTerms::make()->stockDelivery($this->stockDelivery))) {
            $validator->errors()->add('agreed_terms', __('The invoice charges more than was agreed. Purchasing must accept each difference with a reason before it is paid.'));
        }
    }

    public function authorize(ActionRequest $request): bool
    {
        return self::canRecord($this->stockDelivery, $request->user());
    }

    public function asController(StockDelivery $stockDelivery, ActionRequest $request): AgentPayment
    {
        abort_unless($stockDelivery->agent_id, 404);
        $this->stockDelivery = $stockDelivery;
        $this->initialisation($stockDelivery->organisation, $request);

        return $this->handle($stockDelivery, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
