<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Feb 2025 11:03:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use Lorisleiva\Actions\ActionRequest;

trait WithBannerAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {

        if ($this->asAction) {
            return true;
        }
        if ($this->shop->type == ShopTypeEnum::FULFILMENT) {
            $fulfilmentID = $this->shop->fulfilment->id;

            $this->canEdit   = $request->user()->authTo([
                "web.{$this->shop->id}.edit",
                "supervisor-fulfilment-shop.".$fulfilmentID,
                "fulfilment-shop.$fulfilmentID.edit"
            ]);
            $this->canDelete = $this->canEdit;


            return $request->user()->authTo([
                "web.{$this->shop->id}.view",
                "supervisor-fulfilment-shop.".$fulfilmentID,
                "fulfilment-shop.$fulfilmentID.view"
            ]);
        }
        $this->canEdit   = $request->user()->authTo("web.{$this->shop->id}.edit");
        $this->canDelete = $this->canEdit;
        return $request->user()->authTo("web.{$this->shop->id}.view");

    }
}
