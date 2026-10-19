<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Enums\SysAdmin\Authorisation\WarehousePermissionsEnum;
use Lorisleiva\Actions\ActionRequest;

trait WithOrganisationDispatchingViewAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo(WarehousePermissionsEnum::getDispatchingViewPermissionNames($this->organisation));
    }
}
