<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 10:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use Lorisleiva\Actions\ActionRequest;

trait WithDispatchingEquipmentAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $this->canEdit = $request->user()->authTo([
            "supervisor-locations.{$this->warehouse->id}",
            "locations.{$this->warehouse->id}.edit",
        ]);
        $this->canDelete = $this->canEdit;

        return $request->user()->authTo([
            "org-admin.{$this->organisation->id}",
            "dispatching.{$this->warehouse->id}.view",
            "fulfilment.{$this->warehouse->id}.view",
            "supervisor-locations.{$this->warehouse->id}",
            "locations.{$this->warehouse->id}.view",
        ]);
    }
}
