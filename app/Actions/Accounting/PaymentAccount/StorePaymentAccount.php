<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount;

use App\Actions\Accounting\PaymentServiceProvider\Hydrators\PaymentServiceProviderHydrateAccounts;
use App\Actions\InertiaOrganisationAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateAccounting;
use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePaymentAccount extends InertiaOrganisationAction
{
    private bool $asAction = false;

    public function handle(PaymentServiceProvider $paymentServiceProvider, array $modelData): PaymentAccount
    {
        data_set($modelData, 'group_id', $paymentServiceProvider->group_id);
        data_set($modelData, 'organisation_id', $paymentServiceProvider->organisation_id);
        /** @var PaymentAccount $paymentAccount */
        $paymentAccount = $paymentServiceProvider->accounts()->create($modelData);
        $paymentAccount->stats()->create();
        PaymentServiceProviderHydrateAccounts::dispatch($paymentServiceProvider);
        OrganisationHydrateAccounting::dispatch($paymentServiceProvider->organisation);

        return $paymentAccount;
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
            'type'      => ['required', Rule::in(PaymentAccountTypeEnum::values())],
            'code'      => [
                'required',
                'between:2,16',
                'alpha_dash',
                new IUnique(
                    table: 'payment_accounts',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                    ]
                ),
            ],
            'name'       => ['required', 'max:250', 'string'],
            'is_accounts'=> ['sometimes', 'boolean'],
            'source_id'  => ['sometimes', 'string'],
        ];
    }

    public function action(PaymentServiceProvider $paymentServiceProvider, array $modelData): PaymentAccount
    {
        $this->asAction = true;
        $this->initialisation($paymentServiceProvider->organisation, $modelData);

        return $this->handle($paymentServiceProvider, $this->validatedData);
    }
}
