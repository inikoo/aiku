<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jul 2023 13:31:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;

trait WithOrganisationsArgument
{
    protected function getOrganisations(Command $command): LazyCollection
    {
        return Organisation::query()->where('type', 'shop')
            ->when($command->argument('organisations'), function ($query) use ($command) {
                $query->whereIn('slug', $command->argument('organisations'));
            })
            ->cursor();
    }
}
