<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Oct 2023 08:55:05 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Snapshot;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Web\Slide\StoreSlide;
use App\Models\Helpers\Snapshot;
use App\Models\Web\Banner;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StoreBannerSnapshot extends OrgAction
{
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
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

        return DB::transaction(function () use ($banner, $modelData, $slides) {
            /** @var Snapshot $snapshot */
            $snapshot = $banner->snapshots()->create($modelData);
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
        });
    }

    public function rules(): array
    {
        $rules = [
            'layout' => ['required', 'array'],
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    /**
     * @throws \Throwable
     */
    public function action(Banner $banner, array $modelData, ?array $slides, int $hydratorsDelay = 0, bool $strict = true): Snapshot
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;


        $this->initialisationFromShop($banner->shop, $modelData);

        return $this->handle($banner, $this->validatedData, $slides);
    }

}
