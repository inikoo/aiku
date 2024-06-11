<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 12:34:58 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Models\Helpers\Snapshot;
use App\Models\Web\Webpage;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class WebpageHydrateSnapshots
{
    use AsAction;
    use WithEnumStats;

    private Webpage $webpage;

    public function __construct(Webpage $webpage)
    {
        $this->webpage = $webpage;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->webpage->id))->dontRelease()];
    }

    public function handle(Webpage $webpage): void
    {
        $stats = [
            'number_snapshots' => $webpage->snapshots()->count(),
        ];

        $stats=array_merge($stats, $this->getEnumStats(
            model:'snapshots',
            field: 'state',
            enum: SnapshotStateEnum::class,
            models: Snapshot::class,
            where: function ($q) use ($webpage) {
                $q->where('parent_id', $webpage->id);
                $q->where('parent_id', $webpage->id);
            }
        ));

        $stats=array_merge($stats, $this->getEnumStats(
            model:'webpages',
            field: 'type',
            enum: WebpageTypeEnum::class,
            models: Webpage::class,
            where: function ($q) use ($webpage) {
                $q->where('parent_id', $webpage->id);
            }
        ));

        $stats=array_merge($stats, $this->getEnumStats(
            model:'webpages',
            field: 'purpose',
            enum: WebpagePurposeEnum::class,
            models: Webpage::class,
            where: function ($q) use ($webpage) {
                $q->where('parent_id', $webpage->id);
            }
        ));

        $webpage->stats()->update($stats);
    }


}
