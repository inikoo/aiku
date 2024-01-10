<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Nov 2023 15:02:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Tag;

use App\Actions\Helpers\Tag\Hydrators\TagHydrateCustomers;
use App\Actions\Helpers\Tag\Hydrators\TagHydrateProspects;
use App\Actions\Helpers\Tag\Hydrators\TagHydrateSubjects;
use App\Actions\HydrateModel;

use App\Models\Helpers\Tag;
use Illuminate\Support\Collection;

class HydrateTag extends HydrateModel
{
    public function handle(Tag $tag): void
    {
        TagHydrateSubjects::run($tag);
        if ($tag->type == 'crm') {
            TagHydrateCustomers::run($tag);
            TagHydrateProspects::run($tag);
        }

    }

    public string $commandSignature = 'hydrate:tags {slugs?*}';

    protected function getModel(string $slug): Tag
    {
        return Tag::firstWhere($slug);
    }

    protected function getAllModels(): Collection
    {
        return Tag::get();
    }

}
