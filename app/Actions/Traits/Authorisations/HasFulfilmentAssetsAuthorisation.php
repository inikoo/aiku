<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 25 Feb 2024 10:01:43 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\ActionRequest;

trait HasFulfilmentAssetsAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        if ($request->user() instanceof WebUser) {

            return true;
        }

        if ($this->parent instanceof FulfilmentCustomer or $this->parent instanceof Fulfilment) {
            $this->canEdit = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
        } elseif ($this->parent instanceof Warehouse or $this->parent instanceof Location) {
            $this->canEdit = $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.edit");

            return $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.view");
        }


        return false;
    }
}
