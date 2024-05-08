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

class AttachPaymentAccountToShop
{
    use WithActionUpdate;

    public function handle(Shop $shop, PaymentAccount $paymentAccount): Shop
    {
        $shop->paymentAccounts()->attach(
            $paymentAccount,
            [
                'currency_id' => $shop->currency_id
            ]
        );

        $shop->orgPaymentServiceProviders()->attach(
            $paymentAccount->paymentServiceProvider,
            [
                'currency_id' => $shop->currency_id
            ]
        );

        ShopHydratePaymentAccounts::run($shop);

        return $shop;
    }

    public function asController(Shop $shop, PaymentAccount $paymentAccount): Shop
    {
        return $this->handle($shop, $paymentAccount);
    }
}
