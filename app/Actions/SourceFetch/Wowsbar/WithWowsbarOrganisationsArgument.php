<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 Apr 2024 15:18:11 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Wowsbar;

use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;

trait WithWowsbarOrganisationsArgument
{
    protected function getOrganisations(Command $command): LazyCollection
    {
        return Organisation::query()->where('type', OrganisationTypeEnum::DIGITAL_AGENCY->value)
            ->when($command->argument('organisations'), function ($query) use ($command) {
                $query->whereIn('slug', $command->argument('organisations'));
            })
            ->cursor();
    }
}
