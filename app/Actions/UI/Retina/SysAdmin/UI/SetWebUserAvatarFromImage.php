<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\SysAdmin\UI;

use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithUpdateModelImage;
use App\Models\CRM\WebUser;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class SetWebUserAvatarFromImage
{
    use AsAction;
    use WithActionUpdate;
    use WithUpdateModelImage;


    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     */
    public function handle(WebUser $webUser, string $imagePath, string $originalFilename, string $extension = null): User
    {
        return $this->updateModelImage(
            model: $webUser,
            collection: 'profile',
            field: 'avatar_id',
            imagePath: $imagePath,
            originalFilename: $originalFilename,
            extension: $extension
        );
    }
}
