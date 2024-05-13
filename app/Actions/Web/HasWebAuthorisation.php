<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Feb 2024 15:14:21 Malaysia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web;

use App\Models\Fulfilment\Fulfilment;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

trait HasWebAuthorisation
{
    private Organisation|Fulfilment|Shop $scope;
    public function authorize(ActionRequest $request): bool
    {
        if ($this->scope instanceof Organisation) {
            $this->canEdit = $request->user()->hasPermissionTo("shops.{$this->organisation->id}.edit");

            return $request->user()->hasPermissionTo("shops.{$this->organisation->id}.view");
        } elseif ($this->scope instanceof Shop) {
            $this->canEdit = $request->user()->hasPermissionTo("web.{$this->shop->id}.edit");

            return $request->user()->hasPermissionTo("web.{$this->shop->id}.view");
        } elseif ($this->scope instanceof Fulfilment) {
            $this->canEdit = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
        }

        return false;
    }
}
