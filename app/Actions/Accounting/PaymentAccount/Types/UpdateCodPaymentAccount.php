<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount\Types;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateCodPaymentAccount extends OrgAction
{
    use WithActionUpdate;

    public OrgPaymentServiceProvider|PaymentServiceProvider $parent;

    public function handle(PaymentAccount $paymentAccount, array $modelData): PaymentAccount
    {
        data_set($modelData, 'data', [
            'country_id'     => Arr::get($modelData, 'country_id'),
            'extra_charge'   => Arr::get($modelData, 'extra_charge')
        ]);

        return $this->update($paymentAccount, Arr::only($modelData, 'data'), ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo("accounting.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'countries'           => ['sometimes', 'array', 'exists:countries,id'],
            'extra_charge'         => ['required', 'numeric', 'between:0,100']
        ];
    }

    public function action(PaymentAccount $paymentAccount, array $modelData): PaymentAccount
    {
        $this->asAction = true;
        $this->initialisation($paymentAccount->organisation, $modelData);

        return $this->handle($paymentAccount, $this->validatedData);
    }
}
