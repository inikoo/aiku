<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 12:17:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Manufacturing\Production\ProductionStateEnum;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateProductions
{
    use AsAction;
    use WithEnumStats;

    private Organisation $organisation;

    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->organisation->id))->dontRelease()];
    }


    public function handle(Organisation $organisation): void
    {


        $stats = [
            'number_productions'                  => $organisation->productions()->count(),
        ];


        $stats=array_merge($stats, $this->getEnumStats(
            model:'productions',
            field: 'state',
            enum: ProductionStateEnum::class,
            models: Production::class,
            where: function ($q) use ($organisation) {
                $q->where('organisation_id', $organisation->id);
            }
        ));

        $organisation->manufactureStats()->update($stats);
    }
}
