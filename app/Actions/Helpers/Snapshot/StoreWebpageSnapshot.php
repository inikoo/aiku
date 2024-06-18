<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Oct 2023 10:38:27 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Snapshot;

use App\Actions\Web\Webpage\Hydrators\WebpageHydrateSnapshots;
use App\Enums\Helpers\Snapshot\SnapshotScopeEnum;
use App\Models\Helpers\Snapshot;
use App\Models\Web\Webpage;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebpageSnapshot
{
    use AsAction;

    public function handle(Webpage $webpage, array $modelData): Snapshot
    {

        data_set(
            $modelData,
            'scope',
            SnapshotScopeEnum::WEBPAGE
        );

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
        $webpage->snapshots()->save($snapshot);
        $snapshot->generateSlug();
        $snapshot->saveQuietly();


        WebpageHydrateSnapshots::dispatch($webpage);

        return $snapshot;
    }
}
