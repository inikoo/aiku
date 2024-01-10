<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Tag;

use App\Actions\InertiaAction;
use App\Models\Helpers\Tag;
use Lorisleiva\Actions\Concerns\AsObject;

class GetTagOptions extends InertiaAction
{
    use AsObject;

    public function handle(): array
    {
        $selectOptions = [];
        /** @var Tag $tag */
        foreach (Tag::all() as $tag) {
            $selectOptions[$tag->id] =
                [
                    'id'   => $tag->id,
                    'slug' => $tag->slug,
                    'name' => $tag->name
                ];
        }

        return $selectOptions;
    }
}
