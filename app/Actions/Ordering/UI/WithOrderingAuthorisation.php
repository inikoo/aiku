<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Jan 2025 17:06:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\UI;

use Lorisleiva\Actions\ActionRequest;

trait WithOrderingAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("orders.{$this->shop->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("orders.{$this->shop->id}.edit");

        return $request->user()->hasAnyPermission(
            [
                "orders.{$this->shop->id}.view",
                "accounting.{$this->shop->organisation_id}.view"
            ]
        );
    }
}
