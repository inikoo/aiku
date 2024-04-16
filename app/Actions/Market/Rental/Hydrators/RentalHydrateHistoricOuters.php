<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 02:06:15 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Rental\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Market\Rental;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class RentalHydrateHistoricOuters
{
    use AsAction;
    use WithEnumStats;
    private Rental $rental;

    public function __construct(Rental $rental)
    {
        $this->rental = $rental;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->rental->id))->dontRelease()];
    }
    public function handle(Rental $rental): void
    {

        $stats         = [
            'number_historic_outerables' => $rental->historicRecords()->count(),
        ];

        $rental->update($stats);
    }

}
