<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:25:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Actions\HydrateModel;
use App\Models\SysAdmin\WebUser;
use Illuminate\Support\Collection;

class HydrateWebUser extends HydrateModel
{
    public string $commandSignature = 'hydrate:web-user {organisations?*} {--i|id=}';


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


    protected function getModel(string $slug): WebUser
    {
        return WebUser::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return WebUser::withTrashed()->get();
    }
}
