<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 05:47:05 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Actions\Helpers\Avatars\GetDiceBearAvatar;
use App\Enums\Helpers\Avatars\DiceBearStylesEnum;
use App\Models\CRM\WebUser;
use App\Models\Media\Media;
use App\Models\SysAdmin\User;
use Exception;
use Lorisleiva\Actions\Concerns\AsAction;

trait WithSetUserableAvatar
{
    use AsAction;

    public function handle(User|WebUser $userable, bool $saveHistory = true): array
    {
        $seed = $userable->slug;
        try {
            /** @var Media $media */
            $media = $userable->addMediaFromString(GetDiceBearAvatar::run(DiceBearStylesEnum::IDENTICON, $seed))
                ->preservingOriginal()
                ->withProperties(
                    [
                        'group_id' => $userable->group_id
                    ]
                )
                ->usingName($userable->slug."-avatar")
                ->usingFileName($userable->slug."-avatar.sgv")
                ->toMediaCollection('avatar');

            $avatarID = $media->id;

            if ($saveHistory) {
                $userable->update(['avatar_id' => $avatarID]);
            } else {
                $userable->updateQuietly(['avatar_id' => $avatarID]);
            }

            return ['result' => 'success'];
        } catch (Exception $e) {
            return ['result' => 'error', 'message' => $e->getMessage()];
        }
    }
}
