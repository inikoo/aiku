<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 02:07:50 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Subscription\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Subscription;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class SubscriptionHydrateHistoricAssets
{
    use AsAction;
    use WithEnumStats;
    private Subscription $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->subscription->id))->dontRelease()];
    }
    public function handle(Subscription $subscription): void
    {

        $stats         = [
            'number_historic_assets' => $subscription->historicAssets()->count(),
        ];

        $subscription->stats->update($stats);
    }

}
