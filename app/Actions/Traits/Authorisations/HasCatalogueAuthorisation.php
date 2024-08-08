<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 11:44:14 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

trait HasCatalogueAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {

        if($this->asAction){
            return true;
        }

        if ($this->parent instanceof Organisation) {
            $this->canEdit = $request->user()->hasAnyPermission(
                [
                    'org-supervisor.'.$this->organisation->id,
                ]
            );

            return $request->user()->hasAnyPermission(
                [
                    'org-supervisor.'.$this->organisation->id,
                    'shops-view'.$this->organisation->id,
                ]
            );
        } else {
            $this->canEdit = $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
            return $request->user()->hasPermissionTo("products.{$this->shop->id}.view");
        }
    }
}
