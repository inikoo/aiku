<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use Lorisleiva\Actions\ActionRequest;

trait WithAccountingReportsAuthorisation
{
    /**
     * Accounting reports (Sage, Montana, customer credit) are read by the organisation's report
     * readers and accountants; opened from a shop's dashboard they follow its accounting pages.
     */
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $permissions = ["accounting.{$this->organisation->id}.view"];

        if (isset($this->shop)) {
            $permissions[] = "crm.{$this->shop->id}.view";
            $permissions[] = "orders.{$this->shop->id}.view";
        } else {
            $permissions[] = "org-reports.{$this->organisation->id}";
        }

        return $request->user()->authTo($permissions);
    }
}
