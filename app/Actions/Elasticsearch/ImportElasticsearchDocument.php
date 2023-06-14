<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Elasticsearch;

use App\Imports\HistoryImport;
use Exception;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsObject;
use Maatwebsite\Excel\Facades\Excel;

class ImportElasticsearchDocument
{
    use AsObject;
    use AsAction;

    public string $commandSignature = 'elasticsearch:import {fileName}';
    public string $commandDescription = 'Import the data from backup database using csv format';

    public function handle(string $fileName): ?bool
    {
        try {
            Excel::import(
                new HistoryImport(),
                Storage::disk('datasets')->path($fileName),
                null,
                \Maatwebsite\Excel\Excel::CSV
            );

            echo "Success import data \n";

            return false;
        } catch (Exception) {
            echo "Failed import data \n";

            return false;
        }
    }

    public function asCommand(Command $command): ?bool
    {
        if (!Storage::disk('datasets')->exists($command->argument('fileName'))) {
            $command->error('File doesnt exists');

            return false;
        }

       return $this->handle($command->argument('fileName'));
    }
}
