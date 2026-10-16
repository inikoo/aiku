<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 09:48
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentWarehouseReceipt;

use App\Actions\Helpers\Media\SaveModelAttachment;
use App\Actions\OrgAction;
use App\Actions\Procurement\PurchaseOrder\MarkPurchaseOrderStage;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\Procurement\AgentWarehouseReceipt;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Corrects a received batch, or records the check of it: how many units were rejected, why, with photos. Rejected
 * units leave the agent's warehouse stock and are owed again by the supplier. Only while the batch is not packed.
 */
class UpdateAgentWarehouseReceipt extends OrgAction
{
    use WithProcurementEditAuthorisation;

    public const string PHOTO_SCOPE = 'reject_photo';

    private AgentWarehouseReceipt $receipt;

    public function handle(AgentWarehouseReceipt $receipt, User $user, array $modelData): AgentWarehouseReceipt
    {
        $photos = Arr::pull($modelData, 'photos', []);

        DB::transaction(function () use ($receipt, $user, $modelData) {
            $purchaseOrderTransaction = PurchaseOrderTransaction::lockForUpdate()->findOrFail($receipt->purchase_order_transaction_id);

            $quantityReceived = (float) Arr::get($modelData, 'quantity_received', $receipt->quantity_received);
            $quantityRejected = array_key_exists('quantity_rejected', $modelData) ? $modelData['quantity_rejected'] : $receipt->quantity_rejected;

            if ($quantityRejected !== null && (float) $quantityRejected > $quantityReceived) {
                throw ValidationException::withMessages(
                    array_key_exists('quantity_rejected', $modelData)
                        ? ['quantity_rejected' => __('Only :received units were received: no more than that can be rejected', ['received' => trimDecimalZeros($quantityReceived)])]
                        : ['quantity_received' => __(':rejected units of this batch were rejected: units received cannot be less than :rejected', ['rejected' => trimDecimalZeros($quantityRejected)])]
                );
            }

            StoreAgentWarehouseReceipt::ensureNotOverOrdered($purchaseOrderTransaction, $quantityReceived - (float) $quantityRejected, $receipt);

            if (array_key_exists('quantity_rejected', $modelData) && $quantityRejected !== null) {
                $modelData['checked_at']    = now();
                $modelData['checked_by_id'] = $user->id;
            }

            $receipt->update($modelData);
            $receipt->agentWarehouseStock?->update(['quantity' => $receipt->quantityAccepted()]);
        });

        self::savePhotos($receipt, $photos);

        return $receipt;
    }

    /**
     * @param array<int, UploadedFile> $photos
     */
    public static function savePhotos(AgentWarehouseReceipt $receipt, array $photos): void
    {
        foreach ($photos as $photo) {
            SaveModelAttachment::make()->action($receipt, [
                'path'         => $photo->getPathName(),
                'originalName' => $photo->getClientOriginalName(),
                'extension'    => $photo->getClientOriginalExtension(),
                'scope'        => self::PHOTO_SCOPE,
                'caption'      => __('Rejected :code', ['code' => $receipt->supplierProduct?->code]),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'received_at'       => ['sometimes', 'required', 'date', 'before_or_equal:'.Carbon::parse(MarkPurchaseOrderStage::today($this->organisation))->addDay()->toDateString()],
            'quantity_received' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'quantity_rejected' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'reject_reason'     => ['sometimes', 'nullable', 'string', 'max:5000'],
            'photos'            => ['sometimes', 'array', 'max:10'],
            'photos.*'          => ['file', 'mimes:jpg,jpeg,png,webp,heic', 'max:20480'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->receipt->isEditable()) {
            $validator->errors()->add('quantity_received', __('These goods are already packed for a container: the receipt can no longer be changed'));
        }
    }

    public function asController(AgentWarehouseReceipt $agentWarehouseReceipt, ActionRequest $request): AgentWarehouseReceipt
    {
        $this->receipt = $agentWarehouseReceipt;
        $this->initialisation($agentWarehouseReceipt->agent->organisation, $request);

        return $this->handle($agentWarehouseReceipt, $request->user(), $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
