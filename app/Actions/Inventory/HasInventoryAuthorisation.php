<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 05 Aug 2024 15:18:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory;

use Lorisleiva\Actions\ActionRequest;

trait HasInventoryAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {

        $this->canEdit=$request->user()->hasPermissionTo("inventory.{$this->organisation->id}.edit");
        return $request->user()->hasPermissionTo("inventory.{$this->organisation->id}.view");
    }
}
