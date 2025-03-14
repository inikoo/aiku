<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:34:03 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Media\Hydrators;

use App\Models\Helpers\Media;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class MediaHydrateUsage implements ShouldBeUnique
{
    use AsAction;

    private Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->media->id))->dontRelease()];
    }

    public function handle(Media $media): void
    {
        $usage = DB::table('model_has_media')->where('media_id', $media->id)->where('group_id', $media->group_id)->count();
        $media->update(['usage' => $usage]);
    }


}
