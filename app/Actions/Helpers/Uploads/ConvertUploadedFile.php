<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 16 Aug 2023 08:09:28 Malaysia Time, Pantai Lembeng, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Uploads;

use finfo;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class ConvertUploadedFile
{
    use AsAction;
    use WithAttributes;

    public function handle(string $filename): UploadedFile
    {
        $fInfo    = new finfo(FILEINFO_MIME_TYPE);
        $fullPath = base_path($filename);

        return new UploadedFile(
            $fullPath,
            $filename,
            $fInfo->file($fullPath),
            filesize($fullPath),
            0
        );
    }
}
