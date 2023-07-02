<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jul 2023 12:56:23 Malaysia Time, Sanur, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\WebUser;

use App\Actions\HydrateModel;
use App\Models\Auth\WebUser;
use Illuminate\Support\Collection;

class HydrateWebUser extends HydrateModel
{
    public string $commandSignature = 'hydrate:web-user {tenants?*} {--i|id=}';


    public function handle(WebUser $webUser): void
    {
        $this->tokens($webUser);
    }

    public function tokens(WebUser $webUser): void
    {
        $webUser->update(
            [
               'number_api_tokens'=> $webUser->tokens->count()
            ]
        );
    }


    protected function getModel(int $id): WebUser
    {
        return WebUser::find($id);
    }

    protected function getAllModels(): Collection
    {
        return WebUser::withTrashed()->get();
    }
}
