<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 02:07:50 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

 namespace App\Actions\Catalogue\Charge\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Subscription;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ChargeHydrateHistoricAssets
{
    use AsAction;
    use WithEnumStats;
    private Charge $charge;

    public function __construct(Charge $charge)
    {
        $this->charge = $charge;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->charge->id))->dontRelease()];
    }
    public function handle(Charge $charge): void
    {

        $stats         = [
            'number_historic_assets' => $charge->historicAssets()->count(),
        ];

        $charge->stats->update($stats);
    }

}
