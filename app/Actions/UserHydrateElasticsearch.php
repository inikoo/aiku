<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 26 May 2023 15:03:56 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions;

use App\Actions\Traits\WithElasticsearch;
use App\Models\Auth\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class UserHydrateElasticsearch implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;
    use WithElasticsearch;

    public function handle(string $indexName, User $user): void
    {
        $this->storeElastic($indexName, $user);
    }
}
