<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 17:41:54 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest;

use App\Actions\SysAdmin\Guest\Hydrators\GuestHydrateUniversalSearch;
use App\Models\SysAdmin\Guest;
use Lorisleiva\Actions\Concerns\AsAction;

class GuestsAgentUniversalSearch
{
    use asAction;

    public string $commandSignature = 'guests:search';


    public function handle(Guest $agent): void
    {
        GuestHydrateUniversalSearch::run($agent);
    }

    public function asCommand(): int
    {
        foreach(Guest::all() as $agent) {
            $this->handle($agent);
        }
        return 0;
    }
}
