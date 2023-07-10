<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 05 Jul 2023 13:43:20 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Google\Drive;

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
