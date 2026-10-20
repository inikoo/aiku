<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use Lorisleiva\Actions\ActionRequest;

trait WithAccountingPagesAuthorisation
{
    /**
     * Accounting lists follow the page they are opened from: the group and organisation
     * overviews, the accounting module, or a shop's or fulfilment's own accounting.
     */
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $user      = $request->user();
        $routeName = (string) $request->route()?->getName();

        if (str_starts_with($routeName, 'grp.overview.')) {
            return $user->authTo('group-overview');
        }

        if (str_starts_with($routeName, 'grp.org.overview.')) {
            return $user->authTo("org-reports.{$this->organisation->id}");
        }

        $permissions = ["accounting.{$this->organisation->id}.view"];
        $fulfilment  = $request->route('fulfilment');
        $shop        = $request->route('shop');

        if ($fulfilment instanceof Fulfilment) {
            $permissions[] = "fulfilment-shop.$fulfilment->id.view";
            $permissions[] = "supervisor-fulfilment-shop.$fulfilment->id";
        } elseif ($shop instanceof Shop) {
            $permissions[] = "crm.$shop->id.view";
            $permissions[] = "orders.$shop->id.view";
        }

        return $user->authTo($permissions);
    }
}
