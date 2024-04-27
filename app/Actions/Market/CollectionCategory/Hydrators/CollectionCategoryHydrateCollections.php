<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 07:05:41 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\CollectionCategory\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Market\CollectionCategory;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class CollectionCategoryHydrateCollections
{
    use AsAction;
    use WithEnumStats;

    private CollectionCategory $collectionCategory;

    public function __construct(CollectionCategory $collectionCategory)
    {
        $this->collectionCategory = $collectionCategory;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->collectionCategory->id))->dontRelease()];
    }

    public function handle(CollectionCategory $collectionCategory): void
    {
        $stats = [
            'number_collections' => $collectionCategory->collections()->count(),
        ];

        $collectionCategory->stats()->update($stats);
    }


}
