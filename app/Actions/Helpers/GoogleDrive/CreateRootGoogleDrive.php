<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:33:20 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\GoogleDrive;

use Lorisleiva\Actions\Concerns\AsAction;

class CreateRootGoogleDrive
{
    use AsAction;

    private mixed $service;

    /**
     * @throws \Google\Exception
     */
    public function handle($account): void
    {
        $file_id         = StoreFolderGoogleDrive::run('aurora-'.$account->get('Account Code'), '', ['au_location' => 'root']);
        $aiku_folder_key = $file_id;

        $account->fast_update_json_field('Account Properties', 'google_drive_folder_key', $aiku_folder_key, 'Account Data');
    }
}
