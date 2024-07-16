<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 17:38:48 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Insurance;

use App\Actions\Catalogue\Insurance\Hydrators\InsuranceHydrateUniversalSearch;
use App\Models\Catalogue\Insurance;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateInsuranceUniversalSearch
{
    use asAction;

    public string $commandSignature = 'insurances:search';

    public function handle(Insurance $insurance): void
    {
        InsuranceHydrateUniversalSearch::run($insurance);
    }

    public function asCommand(Command $command): int
    {
        $command->withProgressBar(Insurance::all(), function (Insurance $insurance) {
            $this->handle($insurance);
        });
        return 0;
    }
}
