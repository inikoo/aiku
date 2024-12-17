<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox;

use App\Actions\Helpers\Snapshot\StoreEmailSnapshot;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Comms\Outbox\OutboxStateEnum;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Models\Comms\Outbox;
use App\Models\Helpers\Snapshot;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateWorkshopOutbox extends OrgAction
{
    use WithActionUpdate;

    public function handle(Outbox $outbox, array $modelData): Outbox
    {
        $email = $outbox->emailOngoingRun->email;

        /** @var Snapshot $snapshot */
        $snapshot = StoreEmailSnapshot::run(
            $email,
            [
                'state'          => SnapshotStateEnum::UNPUBLISHED,
                'published_at'   => now(),
                'layout'         => Arr::get($modelData, 'layout'),
                'first_commit'   => false,
                'comment'        => Arr::get($modelData, 'comment'),
                'publisher_id'   => Arr::get($modelData, 'publisher_id'),
                'publisher_type' => Arr::get($modelData, 'publisher_type'),
            ]
        );

        $updateData = [
            'unpublished_snapshot_id'   => $snapshot->id,
            'state'                     => OutboxStateEnum::IN_PROCESS,
            'is_dirty'                  => true
        ];

        return $this->update($email, $updateData);
    }

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string'],
            'layout' => ['required', 'string']
        ];
    }

    public function asController(Outbox $outbox, ActionRequest $request): Outbox
    {
        $this->initialisation($outbox->organisation, $request);

        return $this->handle($outbox, $this->validatedData);
    }
}
