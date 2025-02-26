<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Feb 2025 10:36:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use Lorisleiva\Actions\ActionRequest;

trait WithWebsiteEditAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        if ($this->shop->type == ShopTypeEnum::FULFILMENT) {
            $fulfilmentID = $this->shop->fulfilment->id;

            return $request->user()->authTo([
                "web.{$this->shop->id}.edit",
                "supervisor-fulfilment-shop.".$fulfilmentID,
                "fulfilment-shop.$fulfilmentID.edit"
            ]);
        }

        return $request->user()->authTo("web.{$this->shop->id}.edit");

    }
}
