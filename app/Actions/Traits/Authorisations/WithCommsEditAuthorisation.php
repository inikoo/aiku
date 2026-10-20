<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 17 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\ActionRequest;

trait WithCommsEditAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $this->canEditComms($request->user());
    }

    /**
     * Outbox emails, their workshop, subscribers and email templates are changed by the
     * marketing staff of the shop they belong to, or by fulfilment staff for a fulfilment shop.
     */
    protected function canEditComms(User $user): bool
    {
        if (!isset($this->shop)) {
            return false;
        }

        if ($this->shop->type == ShopTypeEnum::FULFILMENT) {
            return $user->authTo([
                "fulfilment-shop.{$this->shop->fulfilment->id}.edit",
                "supervisor-fulfilment-shop.{$this->shop->fulfilment->id}",
            ]);
        }

        return $user->authTo([
            "crm.{$this->shop->id}.edit",
            "marketing.{$this->shop->id}.edit",
            "supervisor-marketing.{$this->shop->id}",
        ]);
    }
}
