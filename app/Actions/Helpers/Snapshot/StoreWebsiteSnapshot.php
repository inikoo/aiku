<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Oct 2023 10:05:33 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Snapshot;

use App\Models\Helpers\Snapshot;
use App\Models\Web\Website;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebsiteSnapshot
{
    use AsAction;

    public function handle(Website $website, array $modelData): Snapshot
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

        $snapshot = Snapshot::create($modelData);
        $website->snapshots()->save($snapshot);
        $snapshot->saveQuietly();
        $snapshot->stats()->create();

        return $snapshot;
    }
}
