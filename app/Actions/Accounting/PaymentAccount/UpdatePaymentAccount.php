<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 14:30:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Accounting\PaymentAccountResource;
use App\Models\Accounting\PaymentAccount;
use Lorisleiva\Actions\ActionRequest;

class UpdatePaymentAccount
{
    use WithActionUpdate;

    private bool $asAction=false;

    public function handle(PaymentAccount $paymentAccount, array $modelData): PaymentAccount
    {
        return $this->update($paymentAccount, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("accounting.edit");
    }
    public function rules(): array
    {
        return [
            'code'         => ['sometimes', 'required', 'unique:payment_accounts', 'between:2,9', 'alpha_dash'],
            'name'         => ['sometimes', 'required', 'max:250', 'string'],
        ];
    }

    public function action(PaymentAccount $paymentAccount, $modelData): PaymentAccount
    {
        $this->asAction=true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($paymentAccount, $validatedData);
    }
    public function asController(PaymentAccount $paymentAccount, ActionRequest $request): PaymentAccount
    {
        $request->validate();
        return $this->handle($paymentAccount, $request->all());
    }


    public function jsonResponse(PaymentAccount $paymentAccount): PaymentAccountResource
    {
        return new PaymentAccountResource($paymentAccount);
    }
}
