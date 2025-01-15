<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 15-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Helpers\Media;

use App\Models\Helpers\Media;
use Illuminate\Support\Facades\File;
use Lorisleiva\Actions\Concerns\AsAction;

class GetNotExistImages
{
    use AsAction;

    public function handle(): void
    {
        $media = Media::all();

        $empties = "";
        foreach ($media as $m) {
            if (!File::exists($m->getPath())) {
                $strempty = 'id: '.$m->id. ' file_name: ' . $m->file_name .' path: '.$m->getPath() . "\n";
                $empties .= $strempty;
                print 'id: '.$m->id. ' file_name:' . $m->file_name. "\n";
            }
        }
        if (!empty($empties)) {
            $filePath = storage_path('logs/list_images_not_exists.log');
            File::append($filePath, $empties);
        }
    }

    public string $commandSignature = 'helper:get_not_exist_image';

    public function asCommand($command)
    {
        $this->handle();
    }
}
