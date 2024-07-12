<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Oct 2023 10:05:33 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\Helpers\Deployment\StoreDeployment;
use App\Actions\Helpers\Snapshot\StoreWebsiteSnapshot;
use App\Actions\Helpers\Snapshot\UpdateSnapshot;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Models\Helpers\Snapshot;
use App\Models\Web\Website;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;

class PublishWebsiteMarginal extends OrgAction
{
    use WithActionUpdate;

    public bool $isAction = false;

    public function handle(Website $website, string $marginal, array $modelData): Website
    {
        $layout = [];
        if ($marginal == 'header') {
            $layout = Arr::get($modelData, 'layout') ?? $website->unpublishedHeaderSnapshot->layout;
        } elseif ($marginal == 'footer') {
            $layout = Arr::get($modelData, 'layout') ?? $website->unpublishedFooterSnapshot->layout;
        }

        $firstCommit = true;

        foreach ($website->snapshots()->where('scope', $marginal)->where('state', SnapshotStateEnum::LIVE)->get() as $liveSnapshot) {
            $firstCommit = false;
            UpdateSnapshot::run($liveSnapshot, [
                'state'           => SnapshotStateEnum::HISTORIC,
                'published_until' => now()
            ]);
        }


        /** @var Snapshot $snapshot */
        $snapshot = StoreWebsiteSnapshot::run(
            $website,
            [
                'state'          => SnapshotStateEnum::LIVE,
                'published_at'   => now(),
                'layout'         => $layout,
                'scope'          => $marginal,
                'first_commit'   => $firstCommit,
                'comment'        => Arr::get($modelData, 'comment'),
                'publisher_id'   => Arr::get($modelData, 'publisher_id'),
                'publisher_type' => Arr::get($modelData, 'publisher_type'),
            ],
        );

        StoreDeployment::run(
            $website,
            [
                'scope'          => $marginal,
                'snapshot_id'    => $snapshot->id,
                'publisher_id'   => Arr::get($modelData, 'publisher_id'),
                'publisher_type' => Arr::get($modelData, 'publisher_type'),
            ]
        );

        $updateData = [
            "live_{$marginal}_snapshot_id"    => $snapshot->id,
            "published_layout->$marginal"     => $snapshot->layout,
            "published_{$marginal}_checksum"  => md5(json_encode($snapshot->layout)),
        ];

        $website->update($updateData);

        return $website;
    }

    public function htmlResponse(Website $website): Response
    {
        return Inertia::location(route('grp.org.shops.show.web.websites.workshop.header', [
            'organisation' => $website->organisation->slug,
            'shop'         => $website->shop->slug,
            'website'      => $website->slug,
        ]));
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;

        if ($this->isAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("websites.edit");
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

    public function rules(): array
    {
        return [
            'comment'        => ['sometimes', 'required', 'string', 'max:1024'],
            'publisher_id'   => ['sometimes'],
            'publisher_type' => ['sometimes', 'string'],
            'layout'         => ['sometimes']
        ];
    }


    public function header(Website $website, ActionRequest $request): Website
    {
        $this->initialisationFromShop($website->shop, $request);
        return $this->handle($website, 'header', $this->validatedData);
    }

    public function footer(Website $website, ActionRequest $request): Website
    {
        $this->initialisationFromShop($website->shop, $request);
        return $this->handle($website, 'footer', $this->validatedData);
    }

    public function action(Website $website, $marginal, $modelData): string
    {
        $this->isAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        $this->handle($website, $marginal, $validatedData);

        return "🚀";
    }


}
