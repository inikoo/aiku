<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 14:30:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount;

use App\Actions\InertiaOrganisationAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Accounting\PaymentAccountResource;
use App\Models\Accounting\PaymentAccount;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdatePaymentAccount extends InertiaOrganisationAction
{
    use WithActionUpdate;

    private bool $asAction = false;

    private PaymentAccount $paymentAccount;

    public function handle(PaymentAccount $paymentAccount, array $modelData): PaymentAccount
    {
        return $this->update($paymentAccount, $modelData, ['data']);
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
            'code' => [
                'sometimes',
                'required',
                'between:2,16',
                'alpha_dash',
                new IUnique(
                    table: 'payment_accounts',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->paymentAccount->id
                        ],
                    ]
                ),
            ],
            'name' => ['sometimes', 'required', 'max:250', 'string'],
        ];
    }

    public function action(PaymentAccount $paymentAccount, $modelData): PaymentAccount
    {
        $this->asAction       = true;
        $this->paymentAccount = $paymentAccount;


        $this->initialisation($paymentAccount->organisation, $modelData);

        return $this->handle($paymentAccount, $this->validatedData);
    }

    public function asController(PaymentAccount $paymentAccount, ActionRequest $request): PaymentAccount
    {
        $this->paymentAccount = $paymentAccount;

        $this->initialisation($paymentAccount->organisation, $request);

        return $this->handle($paymentAccount, $this->validatedData);
    }


    public function jsonResponse(PaymentAccount $paymentAccount): PaymentAccountResource
    {
        return new PaymentAccountResource($paymentAccount);
    }
}
