<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 12:35:47 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Billable\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Billable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class BillableHydrateHistoricOuterables
{
    use AsAction;
    use WithEnumStats;
    private Billable $billable;

    public function __construct(Billable $billable)
    {
        $this->billable = $billable;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->billable->id))->dontRelease()];
    }
    public function handle(Billable $billable): void
    {

        $stats         = [
            'number_historic_outerables' => $billable->historicOuters()->count(),
        ];

        $billable->stats()->update($stats);
    }

}
