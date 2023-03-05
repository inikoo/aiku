<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 14:50:21 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Utils;

use App\Actions\WithActionUpdate;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Guest;

class SetPhoto
{
    use WithActionUpdate;

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(Employee|Guest $subject, string $image_path, string $filename): Employee|Guest
    {
        $checksum = md5_file($image_path);

        if ($subject->getMedia('photo', ['checksum' => $checksum])->count() == 0) {
            $subject->addMedia($image_path)
                ->preservingOriginal()
                ->withCustomProperties(['checksum' => $checksum])
                ->usingName($filename)
                ->usingFileName($checksum.".".pathinfo($image_path, PATHINFO_EXTENSION))
                ->toMediaCollection('photo');
        }


        return $subject;
    }
}
