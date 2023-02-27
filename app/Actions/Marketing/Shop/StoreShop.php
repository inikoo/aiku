<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 01:35:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Marketing\Shop;

use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Marketing\Shop;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreShop
{
    use AsAction;

    public function handle(array $modelData): Shop
    {
        /** @var Shop $shop */
        $shop = Shop::create($modelData);
        $shop->stats()->create();

        $accountsPaymentServiceProvider = PaymentServiceProvider::where('block', 'accounts')->first();
        $account=StorePaymentAccount::run($accountsPaymentServiceProvider, [
            'code'        =>'accounts',
            'currency_id' => $shop->currency_id
        ]);
        $account->slug=$shop->slug.'-accounts';
        $account->save();
        return $shop;
    }

}
