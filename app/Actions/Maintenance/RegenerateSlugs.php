<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 10 Nov 2022 15:50:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Maintenance;

use App\Actions\Traits\WithTenantsArgument;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsCommand;

class RegenerateSlugs
{
    use asCommand;
    use WithTenantsArgument;


    public string $commandSignature = 'maintenance:regenerate-slugs
    {model : model name}
    {tenants?*} ';


    public function asCommand(Command $command): int
    {
        $tenants  = $this->getTenants($command);
        $exitCode = 0;

        foreach ($tenants as $tenant) {
            $result = (int)$tenant->execute(function () use ($command, $tenant) {
                $modelName = match ($command->argument('model')) {
                    'shops' => '\App\Models\Market\Shop',
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
