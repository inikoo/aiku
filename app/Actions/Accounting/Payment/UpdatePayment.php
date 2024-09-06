<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 14:31:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Actions\Accounting\Payment\Hydrators\PaymentHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Accounting\PaymentsResource;
use App\Models\Accounting\Payment;
use Lorisleiva\Actions\ActionRequest;

class UpdatePayment extends OrgAction
{
    use WithActionUpdate;

    public function handle(Payment $payment, array $modelData): Payment
    {
        $payment = $this->update($payment, $modelData, ['data']);
        PaymentHydrateUniversalSearch::dispatch($payment)->delay($this->hydratorsDelay);

        return $payment;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("accounting.edit");
    }

    public function rules(): array
    {
        return [
            'reference'    => ['sometimes', 'nullable', 'max:255', 'string'],
            'amount'       => ['sometimes', 'decimal:0,2'],
            'org_amount'   => ['sometimes', 'numeric'],
            'group_amount' => ['sometimes', 'numeric'],
        ];
    }

    public function action(Payment $payment, array $modelData, int $hydratorsDelay = 0): Payment
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($payment->shop, $modelData);

        return $this->handle($payment, $this->validatedData);
    }


    public function asController(Payment $payment, ActionRequest $request): Payment
    {
        $this->initialisationFromShop($payment->shop, $request);

        return $this->handle($payment, $this->validatedData);
    }

    public function jsonResponse(Payment $payment): PaymentsResource
    {
        return new PaymentsResource($payment);
    }
}
