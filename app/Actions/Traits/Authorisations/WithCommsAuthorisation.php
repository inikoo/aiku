<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use Lorisleiva\Actions\ActionRequest;

trait WithCommsAuthorisation
{
    use WithGroupMarketingReaders;
    use WithCommsEditAuthorisation;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $user      = $request->user();
        $routeName = (string) $request->route()?->getName();

        $this->canEdit = $this->canEditComms($user);

        if ($this->canEdit) {
            return true;
        }

        if (str_ends_with($routeName, '.workshop')) {
            return false;
        }

        $canView = $this->shop->type == ShopTypeEnum::FULFILMENT
            ? $user->authTo("fulfilment-shop.{$this->shop->fulfilment->id}.view")
            : $user->authTo([
                "shop-admin.{$this->shop->id}",
                "marketing.{$this->shop->id}.view",
                "web.{$this->shop->id}.view",
                "orders.{$this->shop->id}.view",
                "crm.{$this->shop->id}.view",
            ]);

        return $canView || $this->readsMarketingAcrossShops($user);
    }
}
