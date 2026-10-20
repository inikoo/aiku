<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use Lorisleiva\Actions\ActionRequest;

trait WithTradeUnitPickerAuthorisation
{
    /**
     * Trade units carry group costs, so they are listed for goods, compliance, masters and supply
     * chain staff, and for product editors of a shop or inventory editors of an organisation, who
     * pick them when they set a product or a stock composition.
     */
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $user = $request->user();

        if ($user->authTo(['goods.view', 'goods.edit', 'compliance.view', 'masters.view', 'masters.edit', 'supply-chain.view'])) {
            return true;
        }

        return $user->getAllPermissions()->contains(
            fn ($permission) => preg_match('/^(products|inventory)\.\d+(\.edit)?$/', $permission->name) === 1
        );
    }
}
