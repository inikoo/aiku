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
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Models\Helpers\Snapshot;
use App\Models\Web\Webpage;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class PublishWebpage
{
    use WithActionUpdate;

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

        $layout = $webpage->unpublishedSnapshot->layout;


        if($layout!='' and  Arr::get($layout, 'html')) {
            $layout['html'] = Arr::get($layout, 'html');
        }

        /** @var Snapshot $snapshot */
        $snapshot = StoreWebpageSnapshot::run(
            $webpage,
            [
                'state'          => SnapshotStateEnum::LIVE,
                'published_at'   => now(),
                'layout'         => $layout,
                'first_commit'   => $firstCommit,
                'comment'        => Arr::get($modelData, 'comment'),
                'publisher_id'   => Arr::get($modelData, 'publisher_id'),
                'publisher_type' => Arr::get($modelData, 'publisher_type'),
            ]
        );

        StoreDeployment::run(
            $webpage,
            [
                'snapshot_id'    => $snapshot->id,
                'publisher_id'   => Arr::get($modelData, 'publisher_id'),
                'publisher_type' => Arr::get($modelData, 'publisher_type'),
            ]
        );

        $compiledLayout = $snapshot->compiledLayout();


        $updateData = [
            'live_snapshot_id'   => $snapshot->id,
            'compiled_layout'    => $compiledLayout,
            'published_checksum' => md5(json_encode($snapshot->layout)),
            'state'              => WebpageStateEnum::LIVE,
        ];

        if ($webpage->state == WebpageStateEnum::IN_PROCESS or $webpage->state == WebpageStateEnum::READY) {
            $updateData['live_at'] = now();
        }

        $webpage->update($updateData);

        return $webpage;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->isAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("websites.edit");
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
                'publisher_type' => 'OrganisationUser'
            ]
        );
    }

    public function asController(Webpage $webpage, ActionRequest $request): string
    {
        $request->validate();
        $this->handle($webpage, $request->validated());

        return "🚀";
    }

    public function action(Webpage $webpage, $modelData): Webpage
    {
        $this->isAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($webpage, $validatedData);
    }
}
