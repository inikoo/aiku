<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 12:35:47 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Outer\Hydrator;

use App\Actions\Traits\WithEnumStats;
use App\Models\Market\Outer;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OuterHydrateHistoricOuters
{
    use AsAction;
    use WithEnumStats;
    private Outer $outer;

    public function __construct(Outer $outer)
    {
        $this->outer = $outer;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->outer->id))->dontRelease()];
    }
    public function handle(Outer $outer): void
    {

        $stats         = [
            'number_historic_outers' => $outer->historicRecords()->count(),
        ];

        $outer->update($stats);
    }

}
