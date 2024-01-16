<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailTemplate;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Mail\EmailTemplate;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AttachImageToEmailTemplate
{
    use AsAction;
    use WithActionUpdate;

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(EmailTemplate $emailTemplate, string $collection, string $imagePath, string $originalFilename, string $extension = null): Media
    {
        $checksum = md5_file($imagePath);
        /** @var Media $media */
        $media = $emailTemplate->media()->where('collection_name', $collection)->where('checksum', $checksum)->first();

        if ($media) {
            return $media;
        }

        $filename = dechex(crc32($checksum)).'.';
        $filename .= empty($extension) ? pathinfo($imagePath, PATHINFO_EXTENSION) : $extension;

        $media= $emailTemplate->addMedia($imagePath)
            ->preservingOriginal()
            ->withProperties(
                [
                    'checksum' => $checksum,
                ]
            )
            ->usingName($originalFilename)
            ->usingFileName($filename)
            ->toMediaCollection($collection);
        $media->refresh();
        return $media;
    }
}
