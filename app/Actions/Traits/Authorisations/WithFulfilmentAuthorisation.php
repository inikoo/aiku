<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 25 Feb 2024 10:01:43 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Group;
use Lorisleiva\Actions\ActionRequest;

trait WithFulfilmentAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if (!isset($this->parent) and isset($this->fulfilment)) {
            $this->parent = $this->fulfilment;
        }

        if ($this->parent instanceof FulfilmentCustomer or $this->parent instanceof Fulfilment or $this->parent instanceof Pallet) {
            $this->canEdit = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");

            return $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.view");
        } elseif ($this->parent instanceof Group) {
            return $request->user()->authTo("group-overview");
        } elseif ($this->parent instanceof Warehouse or $this->parent instanceof Location) {
            $this->canEdit = $request->user()->authTo("fulfilment.{$this->warehouse->id}.edit");

            return $request->user()->authTo("fulfilment.{$this->warehouse->id}.view");
        }


        return false;
    }
}
