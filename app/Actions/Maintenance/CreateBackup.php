<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 10 Nov 2022 15:50:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Maintenance;

use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsCommand;

class CreateBackup
{
    use asCommand;


    public string $commandSignature = 'backup:new
    {--N|name=}';


    public function asCommand(Command $command): int
    {
        $fileName = $command->option('name');
        $fileName .= '.zip';
        $path = 'storage/backups/aiku';
        if ($fileName != null) {
            $command->call('backup:run', ['--filename' => $fileName]);
            $command->info('Backup Successfully at '. $path . '/' .$fileName);
        }else{
            $command->call('backup:run');
            $command->info('Backup Successfully at '. $path . ' folder');
        }
        return 0;
    }

}
