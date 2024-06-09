<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:33:20 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\GoogleDrive;

use Exception;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreFolderGoogleDrive
{
    use AsAction;

    private mixed $service;
    private mixed $aiku_folder_key;

    /**
     * @throws \Google\Exception
     */
    public function handle($store): void
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
            $store_folder_key = StoreFolderGoogleDrive::run(
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
            $store_invoice_folder_key = StoreFolderGoogleDrive::run(
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
