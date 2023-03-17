<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 14:31:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Actions\Accounting\Payment\Hydrators\PaymentHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Accounting\PaymentResource;
use App\Models\Accounting\Payment;
use Lorisleiva\Actions\ActionRequest;

class UpdatePayment
{
    use WithActionUpdate;

    public function handle(Payment $payment, array $modelData): Payment
    {
        $payment = $this->update($payment, $modelData, ['data']);
        PaymentHydrateUniversalSearch::dispatch($payment);
        return $payment;
    }
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("accounting.edit");
    }
    public function rules(): array
    {
        return [
            'amount' => ['sometimes', 'required'],
            'date'   => ['sometimes', 'required'],
        ];
    }


    public function asController(Payment $payment, ActionRequest $request): Payment
    {
        $request->validate();
        return $this->handle($payment, $request->all());
    }


    public function jsonResponse(Payment $payment): PaymentResource
    {
        return new PaymentResource($payment);
    }
}
