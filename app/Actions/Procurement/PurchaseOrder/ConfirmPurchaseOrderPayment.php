<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 06:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\Helpers\Media\SaveModelAttachment;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderAttachmentScopeEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderPaymentMethodEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class ConfirmPurchaseOrderPayment extends OrgAction
{
    use WithActionUpdate;

    private PurchaseOrder $purchaseOrder;

    /**
     * The organisation's purchaser or accountant, or the staff of the agent the order was placed through.
     */
    public static function canConfirm(PurchaseOrder $purchaseOrder, User $user): bool
    {
        $agentOrganisationId = $purchaseOrder->isAgentOrder() ? $purchaseOrder->agent?->organisation_id : null;

        return $user->authTo(array_filter([
            "procurement.$purchaseOrder->organisation_id.edit",
            "accounting.$purchaseOrder->organisation_id.edit",
            $agentOrganisationId ? "procurement.$agentOrganisationId.edit" : null,
        ]));
    }

    public static function acceptsPayments(PurchaseOrder $purchaseOrder): bool
    {
        return !in_array($purchaseOrder->state, [PurchaseOrderStateEnum::IN_PROCESS, PurchaseOrderStateEnum::CANCELLED], true);
    }

    /**
     * Only what is paid before the goods ship is confirmed on the purchase order: a deposit, up to the whole
     * total. The rest is paid on the stock delivery, against the supplier's invoice.
     *
     * @param  array{ paid_at: string, method: string, reference?: string|null, bank_statement?: UploadedFile|null, user_id?: int|null, reason?: string|null}  $modelData
     */
    public function handle(PurchaseOrder $purchaseOrder, array $modelData): PurchaseOrder
    {
        $file = Arr::get($modelData, 'bank_statement');

        $purchaseOrder = $this->update($purchaseOrder, [
            'deposit_paid_at'    => $modelData['paid_at'],
            'deposit_paid_by_id' => Arr::get($modelData, 'user_id'),
            'data'               => [
                'payments' => [
                    'deposit' => [
                        'method'    => $modelData['method'],
                        'reference' => Arr::get($modelData, 'reference'),
                    ],
                ],
            ],
        ], ['data']);

        if ($reason = Arr::get($modelData, 'reason')) {
            MarkPurchaseOrderStage::auditOverride($purchaseOrder, 'deposit_paid_at', null, $purchaseOrder->deposit_paid_at?->toDateString(), $reason);
        }

        if ($file) {
            SaveModelAttachment::make()->action($purchaseOrder, [
                'path'         => $file->getPathName(),
                'originalName' => $file->getClientOriginalName(),
                'extension'    => $file->getClientOriginalExtension(),
                'scope'        => PurchaseOrderAttachmentScopeEnum::BANK_STATEMENT->value,
                'sub_scope'    => 'deposit',
                'caption'      => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            ]);
        }

        return $purchaseOrder;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $this->asAction || self::canConfirm($this->purchaseOrder, $request->user());
    }

    public function rules(): array
    {
        return [
            'paid_at'        => ['required', 'date', 'before_or_equal:tomorrow'],
            'method'         => ['required', Rule::enum(PurchaseOrderPaymentMethodEnum::class)],
            'reference'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'bank_statement' => ['required_unless:method,cash', 'nullable', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,webp,heic'],
            'user_id'        => ['sometimes', 'nullable', 'integer'],
            'reason'         => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!self::acceptsPayments($this->purchaseOrder)) {
            $validator->errors()->add('paid_at', __('A payment can only be confirmed once the purchase order is submitted'));
        } elseif ($this->purchaseOrder->deposit_paid_at) {
            $validator->errors()->add('paid_at', __('This payment is already confirmed'));
        } elseif ((float) $this->purchaseOrder->deposit_amount <= 0) {
            $validator->errors()->add('paid_at', __('Set the deposit before paying it'));
        } elseif (!$this->asAction && $validator->errors()->isEmpty() && !trim((string) $this->get('reason'))) {
            $reasons = MarkPurchaseOrderStage::make()->reasonsNeeded($this->purchaseOrder, 'deposit_paid_at', $this->get('paid_at'));
            if ($reasons) {
                $validator->errors()->add('reason', __('Give a reason: :reasons', ['reasons' => implode('; ', $reasons)]));
            }
        }
    }

    public function asController(PurchaseOrder $purchaseOrder, ActionRequest $request): PurchaseOrder
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, $request);

        return $this->handle($purchaseOrder, [...$this->validatedData, 'user_id' => $request->user()->id]);
    }

    public function action(PurchaseOrder $purchaseOrder, array $modelData): PurchaseOrder
    {
        $this->asAction      = true;
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, $modelData);

        return $this->handle($purchaseOrder, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return redirect()->back();
    }
}
