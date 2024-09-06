<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 14:30:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\UI\Accounting\Traits\HasPaymentAccountUpdateActions;
use App\Http\Resources\Accounting\PaymentAccountsResource;
use App\Models\Accounting\PaymentAccount;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdatePaymentAccount extends OrgAction
{
    use WithActionUpdate;
    use HasPaymentAccountUpdateActions;

    private PaymentAccount $paymentAccount;

    public function handle(PaymentAccount $paymentAccount, array $modelData): PaymentAccount
    {
        $paymentAccount = $this->paymentAccountUpdateActions($paymentAccount->paymentServiceProvider->slug, $paymentAccount, $modelData);

        return $this->update($paymentAccount, Arr::only($modelData, ['code', 'name']), ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes',
                'required',
                'max:16',
                'alpha_dash',
                new IUnique(
                    table: 'payment_accounts',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
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

    public function asController(Organisation $organisation, PaymentAccount $paymentAccount, ActionRequest $request): PaymentAccount
    {
        $this->paymentAccount = $paymentAccount;
        $this->initialisation($paymentAccount->organisation, $request);

        return $this->handle($paymentAccount, $this->validatedData);
    }


    public function jsonResponse(PaymentAccount $paymentAccount): PaymentAccountsResource
    {
        return new PaymentAccountsResource($paymentAccount);
    }
}
