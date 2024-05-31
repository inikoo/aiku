<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 30 May 2024 08:37:52 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Media\Media;

use App\Actions\Helpers\Avatars\GetDiceBearAvatar;
use App\Enums\Helpers\Avatars\DiceBearStylesEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\WebUser;
use App\Models\Media\Media;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Exception;
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
                        'group_id' => $group_id
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
