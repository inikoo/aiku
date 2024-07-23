<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect\Tags\Hydrators;

use App\Models\Helpers\Tag;
use Lorisleiva\Actions\Concerns\AsAction;

class TagHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Tag $tag): void
    {
        $tag->universalSearch()->updateOrCreate(
            [],
            [
                'sections'        => ['crm'],
                'haystack_tier_1' => trim($tag->label),
                'haystack_tier_2' => trim($tag->number_subjects)
            ]
        );
    }

}
