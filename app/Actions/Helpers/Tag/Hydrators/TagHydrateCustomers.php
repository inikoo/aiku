<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Nov 2023 15:02:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Tag\Hydrators;

use App\Models\Helpers\Tag;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class TagHydrateCustomers
{
    use AsAction;

    private Tag $tag;
    public function __construct(Tag $tag)
    {
        $this->tag = $tag;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->tag->id))->dontRelease()];
    }


    public function handle(Tag $tag): void
    {
        $count = DB::table('taggables')->where('tag_id', $tag->id)->where('taggable_type', 'Customer')->count();
        $tag->crmStats()->update(
            [
                'number_customers' => $count,
            ]
        );
    }


}
