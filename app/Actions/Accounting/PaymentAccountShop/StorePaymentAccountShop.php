<?php

/*
 * author Arya Permana - Kirin
 * created on 17-02-2025-15h-36m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\PaymentAccountShop;

use App\Actions\Accounting\PaymentAccount\Hydrators\PaymentAccountHydratePAS;
use App\Actions\OrgAction;
use App\Enums\Accounting\PaymentAccountShop\PaymentAccountShopStateEnum;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentAccountShop;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePaymentAccountShop extends OrgAction
{
    public function handle(PaymentAccount $paymentAccount, Shop $shop, array $modelData): PaymentAccountShop
    {
        data_set($modelData, 'shop', $shop->id);
        /** @var StoredItem $storedItem */
        $paymentAccountShop = $paymentAccount->paymentAccountShops()->create($modelData);

        PaymentAccountHydratePAS::dispatch($paymentAccount);

        return $paymentAccountShop;
    }

    public function rules(): array
    {
        return [
            'state'              => [
                'required',
                Rule::enum(PaymentAccountShopStateEnum::class)
            ],
            'currency_id' => [
                'required',
                'nullable',
                Rule::Exists('currencies', 'id')
            ]
        ];
    }

    public function asController(PaymentAccount $paymentAccount, Shop $shop, ActionRequest $request): PaymentAccountShop
    {
        $this->initialisation($paymentAccount->organisation, $request);

        return $this->handle($paymentAccount, $shop, $this->validateAttributes());
    }

    public function action(PaymentAccount $paymentAccount, Shop $shop, array $modelData): PaymentAccountShop
    {
        $this->asAction           = true;
        $this->initialisation($paymentAccount->organisation, $modelData);

        return $this->handle($paymentAccount, $shop, $this->validateAttributes());
    }

}
