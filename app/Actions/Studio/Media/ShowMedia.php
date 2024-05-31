<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Feb 2024 11:01:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Studio\Media;

use App\Models\Studio\Media;
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
