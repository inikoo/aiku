<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:33:20 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\GoogleDrive;

use Google_Service_Drive_DriveFile;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadFileGoogleDrive
{
    use AsAction;

    private mixed $aiku_folder_key;

    public string $commandSignature = 'drive:upload {filename}';

    /**
     * @throws \Google\Exception
     */
    public function handle($path): string
    {
        $client       = GetClientGoogleDrive::run();
        $organisation = app('currentTenant');
        $name         = Str::of($path)->basename();

        $base_folder_key = Arr::get($organisation->settings, 'google.drive.folder');

        $fileMetadata = new Google_Service_Drive_DriveFile(
            array(
                'name'    => $name,
                'parents' => [$base_folder_key]
            )
        );

        $file = $client->files->create(
            $fileMetadata,
            array(
                'data'       => file_get_contents($path),
                'uploadType' => 'multipart',
                'fields'     => 'id'
            )
        );

        return $file->id;
    }

    /**
     * @throws \Google\Exception
     */
    public function asCommand(Command $command): string
    {
        return $this->handle($command->argument('filename'));
    }
}
