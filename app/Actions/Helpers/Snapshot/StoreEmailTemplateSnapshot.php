<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Oct 2023 08:55:05 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Snapshot;

use App\Enums\Helpers\Snapshot\SnapshotScopeEnum;
use App\Models\Helpers\Snapshot;
use App\Models\Mail\EmailTemplate;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreEmailTemplateSnapshot
{
    use AsAction;

    public function handle(EmailTemplate $emailTemplate, array $modelData): Snapshot
    {
        data_set($modelData, 'layout', $emailTemplate->compiled);
        data_set($modelData, 'scope', SnapshotScopeEnum::EMAIL_TEMPLATE);
        data_set(
            $modelData,
            'checksum',
            md5(
                json_encode(
                    Arr::get($modelData, 'layout')
                )
            )
        );

        $snapshot=Snapshot::create($modelData);
        $emailTemplate->snapshots()->save($snapshot);
        $snapshot->generateSlug();
        $snapshot->saveQuietly();
        $snapshot->stats()->create();

        return $snapshot;
    }
}
