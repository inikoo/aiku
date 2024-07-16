<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 02:07:50 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Insurance\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Insurance;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class InsuranceHydrateHistoricAssets
{
    use AsAction;
    use WithEnumStats;
    private Insurance $insurance;

    public function __construct(Insurance $insurance)
    {
        $this->insurance = $insurance;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->insurance->id))->dontRelease()];
    }
    public function handle(Insurance $insurance): void
    {

        $stats         = [
            'number_historic_assets' => $insurance->historicAssets()->count(),
        ];

        $insurance->stats->update($stats);
    }

}
