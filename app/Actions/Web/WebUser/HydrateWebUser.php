<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 11 Nov 2022 12:09:11 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebUser;

use App\Actions\HydrateModel;
use App\Models\Web\WebUser;
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
