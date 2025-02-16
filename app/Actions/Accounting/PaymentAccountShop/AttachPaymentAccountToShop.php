<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 16 Feb 2025 22:50:23 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccountShop;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydratePaymentAccounts;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Accounting\PaymentAccount;
use App\Models\Catalogue\Shop;

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

        ShopHydratePaymentAccounts::dispatch($shop);

        return $shop;
    }
}
