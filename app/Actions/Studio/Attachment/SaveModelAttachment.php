<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 30 May 2024 08:37:52 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Studio\Attachment;

use App\Actions\Studio\Media\StoreMediaFromFile;
use App\Models\HumanResources\Employee;
use App\Models\Studio\Media;
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
