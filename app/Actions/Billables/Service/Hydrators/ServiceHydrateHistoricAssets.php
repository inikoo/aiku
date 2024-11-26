<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 15:22:15 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Billables\Service\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Billables\Service;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ServiceHydrateHistoricAssets
{
    use AsAction;
    use WithEnumStats;
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->service->id))->dontRelease()];
    }
    public function handle(Service $service): void
    {

        $stats         = [
            'number_historic_assets' => $service->historicAssets()->count(),
        ];

        $service->asset->stats->update($stats);
    }

}
