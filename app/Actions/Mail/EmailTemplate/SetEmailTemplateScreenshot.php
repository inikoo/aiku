<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 13 Dec 2023 04:20:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailTemplate;

use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WIthSaveUploadedImage;
use App\Models\Mail\EmailTemplate;
use Lorisleiva\Actions\Concerns\AsAction;

class SetEmailTemplateScreenshot
{
    use AsAction;
    use WithActionUpdate;
    use WIthSaveUploadedImage;

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(EmailTemplate $emailTemplate, string $imagePath, string $originalFilename, string $extension = null): EmailTemplate
    {
        return $this->saveUploadedImage(
            model: $emailTemplate,
            collection: 'screenshot',
            field: 'screenshot_id',
            imagePath: $imagePath,
            originalFilename: $originalFilename,
            extension: $extension,
        );
    }
}
