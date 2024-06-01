<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 19:35:06 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\TariffCode;

use App\Imports\TariffCodeImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;
use Maatwebsite\Excel\Facades\Excel;

class ImportTariffCodeFromCsv
{
    use AsAction;

    public string $commandSignature   = 'import:tariff_codes {filename}';
    public string $commandDescription = 'Import a tariff code from csv (https://github.com/datasets/harmonized-system)';

    public function handle(string $filename): void
    {
        Excel::import(
            new TariffCodeImport(),
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
