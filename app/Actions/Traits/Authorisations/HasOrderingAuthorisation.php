<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 18:06:06 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

trait HasOrderingAuthorisation
{
    private Organisation|Shop $scope;
    private string $authorisationType = 'view';

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }

        return match ($this->authorisationType) {
            'view' => $this->viewAuthorisation($request),
            'edit','update' => $this->editAuthorisation($request),
            default => false,
        };
    }

    private function viewAuthorisation(ActionRequest $request)
    {
        if ($this->scope instanceof Organisation) {
            $this->canEdit      = $request->user()->hasPermissionTo("org-supervisor.{$this->organisation->id}");
            $this->isSupervisor = $request->user()->hasPermissionTo("org-supervisor.{$this->organisation->id}");

            return $request->user()->hasPermissionTo("websites-view.{$this->organisation->id}");
        } else {
            $this->canEdit      = $request->user()->hasPermissionTo("orders.{$this->shop->id}.edit");
            $this->isSupervisor = $request->user()->hasPermissionTo("supervisor-orders.{$this->shop->id}");

            return $request->user()->hasPermissionTo("orders.{$this->shop->id}.view");
        }
    }

    private function editAuthorisation(ActionRequest $request)
    {
        if ($this->scope instanceof Organisation) {
            return $request->user()->hasPermissionTo("org-supervisor.{$this->organisation->id}");
        } else {
            return $request->user()->hasPermissionTo("orders.{$this->shop->id}.edit");
        }
    }

}
