<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 17:04:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Shop\Hydrators;

use App\Models\Market\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydratePaymentAccounts
{
    use AsAction;

    private Shop $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->shop->id))->dontRelease()];
    }


    public function handle(Shop $shop): void
    {
        $stats = [
            'number_org_payment_service_providers' => $shop->orgPaymentServiceProviders()->count(),
            'number_payment_accounts'              => $shop->paymentAccounts()->count(),
        ];

        $shop->accountingStats()->update($stats);
    }


}
