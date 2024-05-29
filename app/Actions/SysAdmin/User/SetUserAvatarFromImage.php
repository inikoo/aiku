<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 May 2024 19:58:04 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithUpdateModelImage;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class SetUserAvatarFromImage
{
    use AsAction;
    use WithActionUpdate;
    use WithUpdateModelImage;


    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     */
    public function handle(User $user, string $imagePath, string $originalFilename, string $extension = null): User
    {
        return $this->updateModelImage(
            model: $user,
            collection: 'profile',
            field: 'avatar_id',
            imagePath: $imagePath,
            originalFilename: $originalFilename,
            extension: $extension
        );
    }
}
