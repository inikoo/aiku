<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class SetPurchaseOrderReadyToSend extends OrgAction
{
    use WithProcurementEditAuthorisation;

    /**
     * An organisation's word that its draft is finished and can go to the agent with the other
     * organisations' drafts for the same supplier. It commits nothing: the order is still a draft.
     *
     * @throws ValidationException
     */
    public function handle(PurchaseOrder $purchaseOrder, bool $ready, ?string $by = null): PurchaseOrder
    {
        if ($purchaseOrder->state !== PurchaseOrderStateEnum::IN_PROCESS) {
            throw ValidationException::withMessages(['ready' => __('Only a draft purchase order can be marked ready to send')]);
        }

        $data = $purchaseOrder->data ?? [];
        if ($ready) {
            $data['ready_to_send'] = ['by' => $by, 'at' => now()->toIso8601String()];
        } else {
            unset($data['ready_to_send']);
        }

        $purchaseOrder->update(['data' => $data]);

        return $purchaseOrder;
    }

    public function rules(): array
    {
        return [
            'ready' => ['required', 'boolean'],
        ];
    }

    public function asController(PurchaseOrder $purchaseOrder, ActionRequest $request): PurchaseOrder
    {
        $this->initialisation($purchaseOrder->organisation, $request);

        return $this->handle($purchaseOrder, (bool) $this->validatedData['ready'], $request->user()->contact_name ?? $request->user()->username);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
