<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jul 2023 13:31:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Models\Organisation\Organisation;
use Illuminate\Console\Command;

trait WithOrganisationArgument
{
    protected function getOrganisation(Command $command): Organisation
    {
        return Organisation::query()->where('code', $command->argument('organisation'))->firstOrFail();
    }
}
