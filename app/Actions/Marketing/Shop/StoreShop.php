<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 01:35:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Marketing\Shop;

use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreShop
{
    use AsAction;

    public function handle(Tenant $tenant, array $modelData): Shop
    {
        /** @var Shop $shop */
        $shop = Shop::create($modelData);
        $shop->stats()->create();


        $paymentAccount       = StorePaymentAccount::run($tenant->accountsServiceProvider(), [
            'code' => 'accounts',
            'currency_id' => $shop->currency_id
        ]);
        $paymentAccount->slug = 'accounts-'.$shop->slug;
        $paymentAccount->save();

        return AttachPaymentAccountToShop::run($shop, $paymentAccount);
    }

}
