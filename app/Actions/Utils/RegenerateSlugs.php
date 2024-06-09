<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:31:47 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Utils;

use App\Actions\Traits\WithOrganisationsArgument;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsCommand;

class RegenerateSlugs
{
    use asCommand;
    use WithOrganisationsArgument;


    public string $commandSignature = 'maintenance:regenerate-slugs
    {model : model name}
    {organisations?*} ';


    public function asCommand(Command $command): int
    {
        $organisations  = $this->getOrganisations($command);
        $exitCode       = 0;

        foreach ($organisations as $organisation) {
            $result = (int)$organisation->execute(function () use ($command, $organisation) {
                $modelName = match ($command->argument('model')) {
                    'shops' => '\App\Models\Catalogue\Shop',
                    default => null
                };
                if (!$modelName) {
                    return 1;
                }

                /** @noinspection PhpUndefinedMethodInspection */
                foreach ($modelName::all() as $model) {
                    $model->generateSlug();
                    $model->save();
                }


                return 0;
            });

            if ($result !== 0) {
                $exitCode = $result;
            }
        }


        return $exitCode;
    }
}
