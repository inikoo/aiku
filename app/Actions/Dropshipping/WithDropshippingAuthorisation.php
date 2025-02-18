<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Feb 2025 10:48:15 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping;

use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;

trait WithDropshippingAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        if (Str::startsWith($request->route()->getName(), 'grp.fulfilment')) {
            $this->canEdit   = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");
            $this->canDelete = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");

            return $request->user()->authTo(
                [
                    "fulfilment-shop.{$this->fulfilment->id}.view",
                    "accounting.{$this->fulfilment->organisation_id}.view"
                ]
            );
        }


        $this->canEdit = $request->user()->authTo("crm.{$this->shop->id}.edit");

        return $request->user()->authTo(
            [
                "crm.{$this->shop->id}.view",
                "accounting.{$this->shop->organisation_id}.view"
            ]
        );

    }

}
