<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 28 Mar 2024 20:51:28 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue;

use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

trait HasMarketAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
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
