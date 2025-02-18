<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 14:30:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount;

use App\Actions\Accounting\PaymentAccount\Search\PaymentAccountRecordSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\UI\Accounting\Traits\HasPaymentAccountUpdateActions;
use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Http\Resources\Accounting\PaymentAccountsResource;
use App\Models\Accounting\PaymentAccount;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdatePaymentAccount extends OrgAction
{
    use WithActionUpdate;
    use HasPaymentAccountUpdateActions;
    use WithNoStrictRules;

    private PaymentAccount $paymentAccount;

    public function handle(PaymentAccount $paymentAccount, array $modelData): PaymentAccount
    {
        if ($this->strict) {
            $paymentAccount = $this->paymentAccountUpdateActions($paymentAccount->paymentServiceProvider->code, $paymentAccount, $modelData);
            $paymentAccount = $this->update($paymentAccount, Arr::only($modelData, ['code', 'name']), ['data']);
        } else {
            $paymentAccount = $this->update($paymentAccount, $modelData, ['data']);
        }

        PaymentAccountRecordSearch::dispatch($paymentAccount);

        return $paymentAccount;
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
        $rules = [
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

        if (!$this->strict) {
            $rules         = $this->noStrictUpdateRules($rules);
            $rules['data'] = ['sometimes', 'array'];
            $rules['type'] = ['sometimes', 'required', Rule::enum(PaymentAccountTypeEnum::class)];
        }

        return $rules;
    }

    public function action(PaymentAccount $paymentAccount, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): PaymentAccount
    {
        $this->strict = $strict;
        if (!$audit) {
            PaymentAccount::disableAuditing();
        }
        $this->asAction       = true;
        $this->paymentAccount = $paymentAccount;
        $this->hydratorsDelay = $hydratorsDelay;

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
