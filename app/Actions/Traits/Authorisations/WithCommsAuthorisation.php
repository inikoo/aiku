<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

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

        $canView = str_starts_with($routeName, 'grp.org.fulfilments.')
            ? $user->authTo("fulfilment-shop.{$this->fulfilment->id}.view")
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
