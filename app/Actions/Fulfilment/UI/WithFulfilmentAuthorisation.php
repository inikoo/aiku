<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Jan 2025 17:06:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\UI;

use Lorisleiva\Actions\ActionRequest;

trait WithFulfilmentAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

        return $request->user()->hasAnyPermission(
            [
                "fulfilment-shop.{$this->fulfilment->id}.view",
                "accounting.{$this->fulfilment->organisation_id}.view"
            ]
        );
    }
}
