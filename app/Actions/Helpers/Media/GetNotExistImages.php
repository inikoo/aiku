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

        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        $empties = "";
        foreach ($media as $m) {
            // check if file images
            $filename = $m->file_name;
            $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
            if (!in_array(strtolower($fileExtension), $extensions)) {
                continue;
            }

            if (!File::exists($m->getPath())) {
                $strempty = 'id: '.$m->id. ' file_name: ' . $filename .' path: '.$m->getPath() . "\n";
                $empties .= $strempty;
                print 'id: '.$m->id. ' file_name:' . $filename. "\n";
            }
        }
        if (!empty($empties)) {
            $filePath = storage_path('logs/list_images_not_exists.log');
            File::append($filePath, $empties);
            dd('List of images that do not exist has been saved in ' . $filePath);
        }
    }

    public string $commandSignature = 'helper:get_not_exist_image';

    public function asCommand($command)
    {
        $this->handle();
    }
}
