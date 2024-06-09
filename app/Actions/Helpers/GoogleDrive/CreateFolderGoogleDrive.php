<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:33:20 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\GoogleDrive;

use Exception;
use Google_Service_Drive_DriveFile;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateFolderGoogleDrive
{
    use AsAction;

    private mixed $service;
    private mixed $aiku_folder_key;

    /**
     * @throws \Google\Exception
     */
    public function handle($name, $parent_key, $app_properties): string
    {
        $folder_data = array(
            'name'          => $name,
            'mimeType'      => 'application/vnd.google-apps.folder',
            'appProperties' => $app_properties
        );

        if ($parent_key != '') {
            $folder_data['parents'] = [$parent_key];
        }

        $fileMetadata = new Google_Service_Drive_DriveFile($folder_data);
        $file         = $this->service->files->create(
            $fileMetadata,
            array(
                'fields' => 'id'
            )
        );

        return $file->id;
    }

    public function setStoreFolder($store): void
    {
        if ($store->properties('google_drive_folder_key')) {

            try {
                $this->service->files->get($store->properties('google_drive_folder_key'));

                return;
            } catch (Exception $e) {

            }
        }

        $store_folder_key = GetFileGoogleDrive::run(
            $this->aiku_folder_key,
            '',
            'folder',
            [
                'au_location'  => 'store',
                'au_store_key' => $store->id
            ]
        );
        if (!$store_folder_key) {
            $store_folder_key = CreateFolderGoogleDrive::run(
                $store->get('Code'),
                $this->aiku_folder_key,
                [
                    'au_location'  => 'store',
                    'au_store_key' => $store->id
                ]
            );

            $store->fast_update_json_field('Store Properties', 'google_drive_folder_key', $store_folder_key);
        }

        $store_invoice_folder_key = GetFileGoogleDrive::run(
            $store_folder_key,
            'invoices',
            'folder',
            [
                'au_location'  => 'invoices',
                'au_store_key' => $store->id
            ]
        );

        if (!$store_invoice_folder_key) {
            $store_invoice_folder_key = CreateFolderGoogleDrive::run(
                'invoices',
                $store_folder_key,
                [
                    'au_location'  => 'invoices',
                    'au_store_key' => $store->id
                ]
            );

            $store->fast_update_json_field('Store Properties', 'google_drive_folder_invoices_key', $store_invoice_folder_key);
        }
    }
}
