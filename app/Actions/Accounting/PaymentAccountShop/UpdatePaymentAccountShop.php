<?php

/*
 * author Arya Permana - Kirin
 * created on 17-02-2025-16h-03m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\PaymentAccountShop;

use App\Actions\Accounting\PaymentAccount\Hydrators\PaymentAccountHydratePAS;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Accounting\PaymentAccountShop\PaymentAccountShopStateEnum;
use App\Models\Accounting\PaymentAccountShop;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdatePaymentAccountShop extends OrgAction
{
    use WithActionUpdate;
    public function handle(PaymentAccountShop $paymentAccountShop, array $modelData): PaymentAccountShop
    {
        $paymentAccountShop = $this->update($paymentAccountShop, $modelData);

        PaymentAccountHydratePAS::dispatch($paymentAccountShop->paymentAccount);

        return $paymentAccountShop;
    }

    public function rules(): array
    {
        return [
            'state'              => [
                'sometimes',
                Rule::enum(PaymentAccountShopStateEnum::class)
            ],
            'currency_id' => [
                'sometimes',
                'nullable',
                Rule::Exists('currencies', 'id')
            ]
        ];
    }

    public function asController(PaymentAccountShop $paymentAccountShop, ActionRequest $request): PaymentAccountShop
    {
        $this->initialisation($paymentAccountShop->paymentAccount->organisation, $request);

        return $this->handle($paymentAccountShop, $this->validateAttributes());
    }

    public function action(PaymentAccountShop $paymentAccountShop, array $modelData): PaymentAccountShop
    {
        $this->asAction           = true;
        $this->initialisation($paymentAccountShop->paymentAccount->organisation, $modelData);

        return $this->handle($paymentAccountShop, $this->validateAttributes());
    }

}
