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

    public function handle(PaymentAccount $paymentAccount, $modelData): void
    {
        /** @var Shop $shop */
        $shop = Shop::find(Arr::get($modelData, 'shop_id'));

        if($shop) {
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
        } else {
            $paymentAccount->shops()->detach();
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
