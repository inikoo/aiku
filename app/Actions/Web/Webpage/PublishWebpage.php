<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Oct 2023 12:04:50 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\Helpers\Deployment\StoreDeployment;
use App\Actions\Helpers\Snapshot\StoreWebpageSnapshot;
use App\Actions\Helpers\Snapshot\UpdateSnapshot;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Models\Helpers\Snapshot;
use App\Models\Web\Webpage;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class PublishWebpage extends OrgAction
{
    use WithActionUpdate;
    use HasWebAuthorisation;
    use WebpageContentManagement;

    public function handle(Webpage $webpage, array $modelData): Webpage
    {
        $firstCommit = false;
        if ($webpage->state == WebpageStateEnum::IN_PROCESS or $webpage->state == WebpageStateEnum::READY) {
            $firstCommit = true;
        }

        foreach ($webpage->snapshots()->where('state', SnapshotStateEnum::LIVE)->get() as $liveSnapshot) {
            UpdateSnapshot::run($liveSnapshot, [
                'state'           => SnapshotStateEnum::HISTORIC,
                'published_until' => now()
            ]);
        }

        $currentUnpublishedLayout = $webpage->unpublishedSnapshot->layout;



        /** @var Snapshot $snapshot */
        $snapshot = StoreWebpageSnapshot::run(
            $webpage,
            [
                'state'          => SnapshotStateEnum::LIVE,
                'published_at'   => now(),
                'layout'         => $currentUnpublishedLayout,
                'first_commit'   => $firstCommit,
                'comment'        => Arr::get($modelData, 'comment'),
                'publisher_id'   => Arr::get($modelData, 'publisher_id'),
                'publisher_type' => Arr::get($modelData, 'publisher_type'),
            ]
        );

        $deployment=StoreDeployment::run(
            $webpage,
            [
                'snapshot_id'    => $snapshot->id,
                'publisher_id'   => Arr::get($modelData, 'publisher_id'),
                'publisher_type' => Arr::get($modelData, 'publisher_type'),
            ]
        );

        $webpage->stats()->update([
            'last_deployed_at' => $deployment->date
        ]);

        $updateData = [
            'live_snapshot_id'    => $snapshot->id,
            'published_layout'    => $snapshot->layout,
            'published_checksum'  => md5(json_encode($snapshot->layout)),
            'state'               => WebpageStateEnum::LIVE,
            'is_dirty'            => false,
        ];

        if ($webpage->state == WebpageStateEnum::IN_PROCESS or $webpage->state == WebpageStateEnum::READY) {
            $updateData['live_at'] = now();
        }

        $webpage->update($updateData);
        return $webpage;
    }

    public function rules(): array
    {
        return [
            'comment'        => ['sometimes', 'required', 'string', 'max:1024'],
            'publisher_id'   => ['sometimes'],
            'publisher_type' => ['sometimes', 'string'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $request->merge(
            [
                'publisher_id'   => $request->user()->id,
                'publisher_type' => 'User'
            ]
        );
    }


    public function jsonResponse(Webpage $webpage): string
    {
        return "🚀";
    }

    public function action(Webpage $webpage, $modelData): Webpage
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($webpage, $validatedData);
    }
}
