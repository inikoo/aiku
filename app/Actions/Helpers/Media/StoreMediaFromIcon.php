<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:34:03 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Media;

use App\Actions\Helpers\Avatars\GetDiceBearAvatar;
use App\Enums\Helpers\Avatars\DiceBearStylesEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\WebUser;
use App\Models\Helpers\Media;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Exception;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreMediaFromIcon
{
    use AsAction;

    public function handle(Group|Organisation|Shop|User|WebUser $model): ?Media
    {
        $seed     = class_basename($model).'-'.$model->slug;
        $iconType = DiceBearStylesEnum::IDENTICON;

        $group_id = $model->group_id;
        if ($model instanceof Group) {
            $iconType = DiceBearStylesEnum::SHAPES;
            $group_id = $model->id;
        } elseif ($model instanceof Organisation) {
            $iconType = DiceBearStylesEnum::RINGS;
        } elseif ($model instanceof Shop) {
            $iconType = DiceBearStylesEnum::BOTS;
        }


        try {
            /** @var Media $media */
            $media = $model->addMediaFromString(GetDiceBearAvatar::run($iconType, $seed))
                ->preservingOriginal()
                ->withProperties(
                    [
                        'group_id' => $group_id,
                        'ulid'     => Str::ulid()
                    ]
                )
                ->usingName($model->slug."-icon")
                ->usingFileName($model->slug."-icon.sgv")
                ->toMediaCollection('icon');


            return $media;
        } catch (Exception) {
            return null;
        }
    }
}
