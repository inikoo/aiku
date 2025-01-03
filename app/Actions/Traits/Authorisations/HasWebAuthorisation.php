<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 03 Jun 2024 23:34:44 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

trait HasWebAuthorisation
{
    private Group|Organisation|Fulfilment|Shop $scope;
    public function authorize(ActionRequest $request): bool
    {

        if ($this->asAction) {
            return true;
        }

        if ($this->scope instanceof Organisation) {
            $this->canEdit      = $request->user()->hasPermissionTo("org-supervisor.{$this->organisation->id}");
            $this->isSupervisor = $request->user()->hasPermissionTo("org-supervisor.{$this->organisation->id}");
            return $request->user()->hasPermissionTo("websites-view.{$this->organisation->id}");
        } elseif ($this->scope instanceof Group) {
            return $request->user()->hasPermissionTo("group-overview");
        } elseif ($this->scope instanceof Shop) {
            $this->canEdit      = $request->user()->hasPermissionTo("web.{$this->shop->id}.edit");
            $this->isSupervisor = $request->user()->hasPermissionTo("supervisor-web.{$this->shop->id}");
            return $request->user()->hasPermissionTo("web.{$this->shop->id}.view");
        } elseif ($this->scope instanceof Fulfilment) {
            $this->canEdit      = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
            $this->isSupervisor = $request->user()->hasPermissionTo("supervisor-fulfilment-shop.{$this->fulfilment->id}");

            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
        }

        return false;
    }
}
