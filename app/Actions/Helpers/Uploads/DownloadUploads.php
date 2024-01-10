<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 16 Aug 2023 08:09:28 Malaysia Time, Pantai Lembeng, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Uploads;

use App\Models\Helpers\Upload;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadUploads
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(Upload $upload): BinaryFileResponse
    {
        return response()->download(storage_path('app/'.$upload->path . '/' . $upload->filename));
    }

    public function asController(Upload $upload): BinaryFileResponse
    {
        return $this->handle($upload);
    }
}
