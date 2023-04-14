<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 01:35:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Marketing\Shop;

use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\Mail\Outbox\StoreOutbox;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Central\Tenant;
use App\Models\Mail\Mailroom;
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
        $shop->accountingStats()->create();
        $shop->mailStats()->create();


        $paymentAccount = StorePaymentAccount::run($tenant->accountsServiceProvider(), [
            'code' => 'accounts-'.$shop->slug,
            'name' => 'Accounts '.$shop->code,
            'data' => [
                'service-code' => 'accounts'
            ]
        ]);
        $paymentAccount->slug = 'accounts-'.$shop->slug;
        $paymentAccount->save();


        foreach (OutboxTypeEnum::cases() as $case) {
            if ($case->scope()=='shop') {
                $mailroom=Mailroom::where('code', $case->mailroomCode()->value)->first();

                StoreOutbox::run(
                    $mailroom,
                    [
                        'shop_id'=> $shop->id,
                        'name'   => $case->label(),
                        'type'   => str($case->value)->camel()->kebab()->value(),

                    ]
                );
            }
        }


        return AttachPaymentAccountToShop::run($shop, $paymentAccount);
    }
}
