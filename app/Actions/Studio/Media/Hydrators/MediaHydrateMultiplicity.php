<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 May 2024 13:47:14 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Studio\Media\Hydrators;

use App\Models\Studio\Media;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class MediaHydrateMultiplicity implements ShouldBeUnique
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
        $multiplicity = DB::table('media')->where('checksum', $media->checksum)->where('group_id', $media->group_id)->count();
        $media->update(['multiplicity' => $multiplicity]);
    }


}
