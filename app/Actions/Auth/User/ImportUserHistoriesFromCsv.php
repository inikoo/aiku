<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 08 Jun 2023 14:34:48 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Imports\HistoryImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;
use Maatwebsite\Excel\Facades\Excel;

class ImportUserHistoriesFromCsv
{
    use AsAction;

    public string $commandSignature   = 'import:user_histories {filename}';
    public string $commandDescription = 'Import user histories from old data';

    public function handle(string $filename): void
    {
        Excel::import(
            new HistoryImport(),
            Storage::disk('datasets')->path($filename),
            null,
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function asCommand(Command $command): int
    {
        if(!Storage::disk('datasets')->exists($command->argument('filename'))) {
            $command->error('File doesnt exists');
            return 1;
        }

        $this->handle($command->argument('filename'));

        return 0;
    }
}
