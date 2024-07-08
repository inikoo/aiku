<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Oct 2023 08:55:05 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Snapshot;

use App\Actions\Web\Slide\StoreSlide;
use App\Models\Helpers\Snapshot;
use App\Models\Web\Banner;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreBannerSnapshot
{
    use AsAction;

    public function handle(Banner $banner, array $modelData, ?array $slides): Snapshot
    {
        data_set(
            $modelData,
            'checksum',
            md5(
                json_encode(
                    Arr::get($modelData, 'layout')
                )
            )
        );

        data_set($modelData, 'scope', 'banner');

        $snapshot=Snapshot::create($modelData);
        $banner->snapshots()->save($snapshot);
        $snapshot->saveQuietly();
        $snapshot->stats()->create();

        if ($slides) {
            foreach ($slides as $slide) {
                StoreSlide::run(
                    snapshot: $snapshot,
                    modelData: $slide,
                );
            }
        }

        return $snapshot;
    }
}
