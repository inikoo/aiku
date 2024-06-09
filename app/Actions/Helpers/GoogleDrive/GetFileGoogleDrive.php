<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:33:20 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\GoogleDrive;

use Lorisleiva\Actions\Concerns\AsAction;

class GetFileGoogleDrive
{
    use AsAction;

    private mixed $service;
    private mixed $aiku_folder_key;

    /**
     * @throws \Google\Exception
     */
    public function handle($parent_folder_key, $name, $type, $metadata): string
    {
        $search = 'trashed=false  ';

        foreach ($metadata as $key => $value) {
            $search .= " and  appProperties has { key='$key' and value='$value' }";
        }

        if ($parent_folder_key != '') {
            $search .= " and parents in '$parent_folder_key'";
        }

        if ($type == 'folder') {
            $search .= " and mimeType = 'application/vnd.google-apps.folder' ";
        }

        if ($name != '') {
            $search .= " and name = '$name' ";
        }


        $optParams = array(
            'pageSize' => 1,
            'fields'   => 'nextPageToken, files(id, name)',
            'q'        => $search
        );
        $results   = @$this->service->files->listFiles($optParams);

        foreach ($results->getFiles() as $file) {
            return $file->getId();
        }

        return false;
    }
}
