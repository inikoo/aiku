<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:34:03 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Attachment;

use App\Actions\Helpers\Media\StoreMediaFromFile;
use App\Models\Helpers\Media;
use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveModelAttachment
{
    use AsAction;

    public function handle(
        Employee $model,
        array $fileData,
        array $modelData
    ): Employee {

        $checksum = md5_file($fileData['path']);

        /** @var Media $media */
        if(!$media=$model->group->media()->where('type', 'attachment')->where('checksum', $checksum)->first()) {
            data_set($fileData, 'checksum', $checksum);
            $media= StoreMediaFromFile::run($model, $fileData, 'attachment', 'attachment');
        }



        $model->attachments()->attach(
            [
                $media->id =>
                    array_merge(
                        $modelData,
                        [
                            'group_id'        => $model->group_id,
                        ]
                    )

            ]
        );

        return $model;

    }

}
