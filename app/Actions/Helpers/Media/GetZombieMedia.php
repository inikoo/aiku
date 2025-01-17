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
use Lorisleiva\Actions\Concerns\AsCommand;

class GetZombieMedia
{
    use AsCommand;


    public string $commandSignature = 'helper:get_zombie_media {--log}';
    public string $commandDescription = 'Get list of images that do not exist in the storage';

    public function asCommand($command): int
    {
        $medias = Media::all();

        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        $empties = "";
        foreach ($medias as $media) {
            // check if file images
            $filename = $media->file_name;
            $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
            if (!in_array(strtolower($fileExtension), $extensions)) {
                continue;
            }

            if (!File::exists($media->getPath())) {
                $strEmpty = 'id: '.$media->id. ' file_name: ' . $filename .' path: '.$media->getPath() . "\n";
                $empties .= $strEmpty;
                print 'id: '.$media->id. ' file_name:' . $filename. "\n";
            }
        }
        if (!empty($empties)  && $command->option('log')) {
            $filePath = storage_path('logs/list_images_not_exists.log');
            File::append($filePath, $empties);
            $command->line('List of images that do not exist has been saved in ' . $filePath);
        }
        return 0;
    }
}
