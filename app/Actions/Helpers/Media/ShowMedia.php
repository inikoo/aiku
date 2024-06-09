<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:34:03 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Media;

use App\Models\Helpers\Media;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ShowMedia
{
    use AsAction;


    public function authorize(): bool
    {
        return true;
    }


    public function asController(Media $media, ActionRequest $request): Media
    {
        return $media;
    }


    public function htmlResponse(Media $media): BinaryFileResponse
    {
        $headers = [
            'Content-Type'   => $media->mime_type,
            'Content-Length' => $media->size,
        ];

        return response()->file($media->getPath(), $headers);
    }
}
