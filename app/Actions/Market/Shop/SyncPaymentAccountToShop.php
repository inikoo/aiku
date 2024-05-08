<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:04:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Market\Shop;

use App\Actions\Market\Shop\Hydrators\ShopHydratePaymentAccounts;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Accounting\PaymentAccount;
use App\Models\Market\Shop;
use Lorisleiva\Actions\ActionRequest;

class SyncPaymentAccountToShop
{
    use WithActionUpdate;

    public function handle(Shop $shop, PaymentAccount $paymentAccount): Shop
    {
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

    public function asController(Shop $shop, PaymentAccount $paymentAccount, ActionRequest $request): Shop
    {
        return $this->handle($shop, $paymentAccount);
    }
}
