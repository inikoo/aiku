<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 00:07:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Media\Media\Hydrators;

use App\Models\Media\Media;
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
        if (!$media->is_fixed) {
            $usage = DB::table('model_has_media')->where('media_id', $media->id)->where('group_id', $media->group_id)->count();
            $media->update(['usage' => $usage]);
        }
    }


}
