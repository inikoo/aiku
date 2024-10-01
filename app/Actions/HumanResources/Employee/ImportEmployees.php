<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\Helpers\Uploads\ImportUpload;
use App\Actions\Helpers\Uploads\StoreUploads;
use App\Actions\Traits\WithImportModel;
use App\Imports\HumanResources\Employee\EmployeeImport;
use App\Models\Helpers\Upload;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;

class ImportEmployees
{
    use WithImportModel;

    /**
     * @var \App\Models\SysAdmin\Organisation
     */
    private Organisation|Workplace $parent;

    public function handle($file): Upload
    {
        $upload = StoreUploads::run($file, Employee::class);

        if ($this->isSync) {
            ImportUpload::run(
                $file,
                new EmployeeImport($this->parent, $upload)
            );
            $upload->refresh();
        } else {
            ImportUpload::dispatch(
                $this->tmpPath.$upload->filename,
                new EmployeeImport($this->parent, $upload)
            );
        }

        return $upload;
    }

    public string $commandSignature = 'employee:import {org} {--g|g_drive} {filename}';

    public function rumImport($file, $command): Upload
    {
        $this->parent = Organisation::where('slug', $command->argument('org'))->first();

        return $this->handle($file);
    }

    public function asController(Organisation $organisation, ActionRequest $request): Upload
    {
        $this->parent = $organisation;
        $request->validate();
        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);

        return $this->handle($file);
    }

    public function inWorkplace(Workplace $workplace, ActionRequest $request): Upload
    {
        $this->parent = $workplace;
        $request->validate();
        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);

        return $this->handle($workplace, $file);
    }

}
