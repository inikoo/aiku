<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 30 Oct 2024 21:54:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Services\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class InverseUlidPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }


    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }


    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }


    protected function getBasePath(Media $media): string
    {
        $path = config('media-library.prefix', '');
        if ($path !== '') {
            $path .= '/';
        }
        $path .= substr($media->ulid, -1).'/'.substr($media->ulid, -2, 1).'/'.$media->ulid;

        return $path;
    }
}
