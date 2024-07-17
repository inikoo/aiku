<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 02:07:50 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shipping\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Shipping;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShippingHydrateHistoricAssets
{
    use AsAction;
    use WithEnumStats;
    private Shipping $shipping;

    public function __construct(Shipping $shipping)
    {
        $this->shipping = $shipping;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->shipping->id))->dontRelease()];
    }
    public function handle(Shipping $shipping): void
    {

        $stats         = [
            'number_historic_assets' => $shipping->historicAssets()->count(),
        ];

        $shipping->stats->update($stats);
    }

}
