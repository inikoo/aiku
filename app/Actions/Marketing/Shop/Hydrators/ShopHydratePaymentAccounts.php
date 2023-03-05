<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 17:04:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Shop\Hydrators;

use App\Models\Marketing\Shop;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydratePaymentAccounts implements ShouldBeUnique
{
    use AsAction;

    public function handle(Shop $shop): void
    {
        $stats = [
            'number_payment_service_providers' => $shop->paymentAccounts()->groupBy('payment_service_provider_id')->pluck('payment_service_provider_id')->count(),
            'number_payment_accounts'          => $shop->paymentAccounts()->count(),
        ];

        $shop->accountingStats()->update($stats);
    }

    public function getJobUniqueId(Shop $shop): string
    {
        return $shop->id;
    }
}
