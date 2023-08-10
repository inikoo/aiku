<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 09 Aug 2023 11:12:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Helpers;

use Lorisleiva\Actions\Concerns\AsObject;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class NaturalLanguage extends DefaultUrlGenerator
{
    use AsObject;

    public function fileSize($size, $precision = 2): string
    {
        if ($size > 0) {
            $size     = (int)$size;
            $base     = log($size) / log(1024);
            $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');

            return round(pow(1024, $base - floor($base)), $precision).$suffixes[floor($base)];
        } else {
            return $size;
        }
    }
}
