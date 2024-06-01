<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Billable\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Catalogue\Outer\OuterStateEnum;
use App\Models\Catalogue\Outer;
use App\Models\Catalogue\Billable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class BillableHydrateOuters
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
            'number_outers' => $billable->outers()->count(),
        ];


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'outers',
                field: 'state',
                enum: OuterStateEnum::class,
                models: Outer::class,
                where: function ($q) use ($billable) {
                    $q->where('billable_id', $billable->id);
                }
            )
        );

        $billable->stats()->update($stats);
    }

}
