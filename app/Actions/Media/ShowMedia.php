<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Mar 2023 15:41:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Media;

use App\Models\Media\Media;
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
