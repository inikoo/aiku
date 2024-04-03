<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Feb 2024 18:16:32 CEST Time, Plane Madrid - Mexico City
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Models\CRM\Customer;
use App\Models\Fulfilment\FulfilmentCustomer;
use Lorisleiva\Actions\ActionRequest;

trait WithAuthorizeWebUserScope
{
    public function authorizeWebUserScope(ActionRequest $request): bool
    {
        if ($this->parent instanceof Customer) {
            $this->canEdit   = $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
            $this->canDelete = $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");

            return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
        } elseif ($this->parent instanceof FulfilmentCustomer) {
            $this->canEdit   = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
            $this->canDelete = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
        }

        return false;
    }
}
