<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use Lorisleiva\Actions\ActionRequest;

trait WithMailshotReadAuthorisation
{
    use WithGroupMarketingReaders;

    /**
     * A mailshot's design and links are read by the CRM and marketing staff of its shop, and by
     * marketing staff of any shop, who reuse other stores' newsletters as templates.
     */
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $user = $request->user();

        $permissions = [
            "crm.{$this->shop->id}.view",
            "crm.{$this->shop->id}.prospects.view",
            "marketing.{$this->shop->id}.view",
            "supervisor-marketing.{$this->shop->id}",
        ];
        if ($this->shop->fulfilment) {
            $permissions[] = "fulfilment-shop.{$this->shop->fulfilment->id}.view";
            $permissions[] = "supervisor-fulfilment-shop.{$this->shop->fulfilment->id}";
        }

        return $user->authTo($permissions) || $this->readsMarketingAcrossShops($user);
    }
}
