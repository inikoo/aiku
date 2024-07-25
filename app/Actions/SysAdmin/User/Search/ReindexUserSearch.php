<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 12:42:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User\Search;

use App\Actions\HydrateModel;
use App\Models\SysAdmin\User;
use Illuminate\Support\Collection;

class ReindexUserSearch extends HydrateModel
{
    public string $commandSignature = 'user:search {organisations?*} {--s|slugs=}';


    public function handle(User $user): void
    {
        UserRecordSearch::run($user);
    }


    protected function getModel(string $slug): User
    {
        return User::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return User::withTrashed()->get();
    }
}
