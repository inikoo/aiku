<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:15 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Banner;

use App\Actions\Helpers\Deployment\StoreDeployment;
use App\Actions\Helpers\Snapshot\StoreBannerSnapshot;
use App\Actions\Helpers\Snapshot\UpdateSnapshot;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Web\Banner\Hydrators\BannerHydrateUniversalSearch;
use App\Actions\Web\Banner\UI\ParseBannerLayout;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Enums\Web\Banner\BannerStateEnum;
use App\Http\Resources\Web\BannerResource;
use App\Models\Helpers\Snapshot;
use App\Models\Web\Banner;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\ActionRequest;

class PublishBanner
{
    use WithActionUpdate;

    public bool $isAction = false;

    public function handle(Banner $banner, array $modelData): Banner
    {
        $firstCommit = false;
        if ($banner->state == BannerStateEnum::UNPUBLISHED) {
            $firstCommit = true;
        }
        foreach ($banner->snapshots()->where('state', SnapshotStateEnum::LIVE)->get() as $liveSnapshot) {
            UpdateSnapshot::run($liveSnapshot, [
                'state'           => SnapshotStateEnum::HISTORIC,
                'published_until' => now()
            ]);
        }

        $layout                = Arr::pull($modelData, 'layout');
        list($layout, $slides) = ParseBannerLayout::run($layout);

        /** @var Snapshot $snapshot */
        $snapshot = StoreBannerSnapshot::run(
            $banner,
            [
                'state'          => SnapshotStateEnum::LIVE,
                'published_at'   => now(),
                'layout'         => $layout,
                'first_commit'   => $firstCommit,
                'publisher_id'   => Arr::get($modelData, 'publisher_id'),
                'publisher_type' => Arr::get($modelData, 'publisher_type'),
                'comment'        => Arr::get($modelData, 'comment'),


            ],
            $slides
        );

        StoreDeployment::run(
            $banner,
            [
                'snapshot_id'    => $snapshot->id,
                'publisher_id'   => Arr::get($modelData, 'publisher_id'),
                'publisher_type' => Arr::get($modelData, 'publisher_type'),
            ]
        );

        $compiledLayout = $snapshot->compiledLayout();


        $updateData = [
            'live_snapshot_id' => $snapshot->id,
            'compiled_layout'  => $compiledLayout,
            'state'            => BannerStateEnum::LIVE,
        ];

        if ($banner->state == BannerStateEnum::UNPUBLISHED) {
            $updateData['live_at'] = now();
            $updateData['date']    = now();
        }

        $banner->update($updateData);
        BannerHydrateUniversalSearch::dispatch($banner);
        UpdateBannerImage::dispatch($banner);


        Cache::put('banner_compiled_layout_'.$banner->ulid, $banner->compiled_layout, 86400);


        return $banner;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->isAction) {
            return true;
        }

        return $request->get('customerUser')->hasPermissionTo("portfolio.banners.edit");
    }

    public function rules(): array
    {
        return [
            'layout'         => ['required', 'array:type,delay,common,components,navigation,published_hash'],
            'comment'        => ['sometimes', 'required', 'string', 'max:1024'],
            'publisher_id'   => ['sometimes'],
            'publisher_type' => ['sometimes', 'string'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $request->merge(
            [
                'layout'         => $request->only(['type', 'delay', 'common', 'components', 'navigation', 'published_hash']),
                'publisher_id'   => $request->get('customerUser')->id,
                'publisher_type' => 'CustomerUser'
            ]
        );
    }

    public function asController(Banner $banner, ActionRequest $request): Banner
    {
        $request->validate();

        return $this->handle($banner, $request->validated());
    }

    public function action(Banner $banner, $modelData): Banner
    {
        $this->isAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($banner, $validatedData);
    }

    public function jsonResponse(Banner $banner): BannerResource
    {
        return new BannerResource($banner);
    }

}
