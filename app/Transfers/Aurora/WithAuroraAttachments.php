<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:10 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mimey\MimeTypes;
use Spatie\TemporaryDirectory\TemporaryDirectory;

trait WithAuroraAttachments
{
    public function getModelAttachmentsCollection($model, $id): Collection
    {
        return DB::connection('aurora')
            ->table('Attachment Bridge as B')
            ->leftJoin('Attachment Dimension as A', 'A.Attachment Key', '=', 'B.Attachment Key')
            ->where('Subject', $model)
            ->where('Subject Key', $id)
            ->get();
    }

    public function fetchAttachment($auroraAttachmentData): array
    {
        $content = $auroraAttachmentData->{'Attachment Data'};

        $temporaryDirectory = TemporaryDirectory::make();

        $mimes = new MimeTypes();


        $temporalName = $auroraAttachmentData->{'Attachment Key'}.'.'.$auroraAttachmentData->{'Attachment File Checksum'};

        $extension = $mimes->getExtension($auroraAttachmentData->{'Attachment MIME Type'});

        if ($extension) {
            $temporalName .= '.'.$extension;
        }



        file_put_contents($temporaryDirectory->path($temporalName), $content);


        return [
            'temporaryDirectory' => $temporaryDirectory,
            'fileData'           => [
                'path'         => $temporaryDirectory->path($temporalName),
                'originalName' => $auroraAttachmentData->{'Attachment File Original Name'},
            ],
            'modelData'          => [
                'scope'   => $auroraAttachmentData->{'Attachment Subject Type'},
                'caption' => $auroraAttachmentData->{'Attachment Caption'},
            ]
        ];
    }
}
