<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 02:07:50 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Service\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Service;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ServiceHydrateHistoricOuters
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
            'number_historic_outerables' => $service->historicRecords()->count(),
        ];

        $service->update($stats);
    }

}
