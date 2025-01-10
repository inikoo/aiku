<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 09 Jan 2025 20:57:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccountShop;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydratePaymentAccounts;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Accounting\PaymentAccount;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class SyncPaymentAccountToShop extends OrgAction
{
    use WithActionUpdate;

    public function handle(PaymentAccount $paymentAccount, $modelData): void
    {
        /** @var Shop $shop */
        $shop = Shop::find(Arr::get($modelData, 'shop_id'));

        if ($shop) {
            $paymentAccount->paymentAccountShops()->syncWithPivotValues(
                $shop,
                [
                    'currency_id' => $shop->currency_id
                ]
            );

            $shop->orgPaymentServiceProviders()->syncWithPivotValues(
                $paymentAccount->paymentServiceProvider,
                [
                    'currency_id' => $shop->currency_id
                ]
            );

            ShopHydratePaymentAccounts::dispatch($shop);
        } else {
            $paymentAccount->paymentAccountShops()->detach();
        }
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['nullable', 'exists:shops,id']
        ];
    }

    public function asController(PaymentAccount $paymentAccount, ActionRequest $request): void
    {
        $this->initialisation($paymentAccount->organisation, $request);

        $this->handle($paymentAccount, $this->validatedData);
    }
}
