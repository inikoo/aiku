<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Feb 2024 18:16:32 CEST Time, Plane Madrid - Mexico City
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Models\CRM\Customer;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Group;
use Lorisleiva\Actions\ActionRequest;

trait WithAuthorizeWebUserScope
{
    public function authorizeWebUserScope(ActionRequest $request): bool
    {
        if ($this->parent instanceof Customer) {
            $this->canEdit   = $request->user()->authTo("crm.{$this->shop->id}.edit");
            $this->canDelete = $request->user()->authTo("crm.{$this->shop->id}.edit");

            return $request->user()->authTo("crm.{$this->shop->id}.edit");
        } elseif ($this->parent instanceof Group) {
            return $request->user()->authTo("group-overview");
        } elseif ($this->parent instanceof FulfilmentCustomer) {
            $this->canEdit   = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");
            $this->canDelete = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");

            return $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.view");
        }

        return false;
    }
}
