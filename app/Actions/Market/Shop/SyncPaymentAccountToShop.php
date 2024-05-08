<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:04:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Market\Shop;

use App\Actions\Market\Shop\Hydrators\ShopHydratePaymentAccounts;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Accounting\PaymentAccount;
use App\Models\Market\Shop;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class SyncPaymentAccountToShop extends OrgAction
{
    use WithActionUpdate;

    public function handle(PaymentAccount $paymentAccount, $modelData): Shop
    {
        /** @var Shop $shop */
        $shop = Shop::findOrFail(Arr::get($modelData, 'shop_id'));

        $paymentAccount->shops()->syncWithPivotValues(
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

        ShopHydratePaymentAccounts::run($shop);

        return $shop;
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'exists:shops,id']
        ];
    }

    public function asController(PaymentAccount $paymentAccount, ActionRequest $request): Shop
    {
        $this->initialisation($paymentAccount->organisation, $request);

        return $this->handle($paymentAccount, $this->validatedData);
    }
}
