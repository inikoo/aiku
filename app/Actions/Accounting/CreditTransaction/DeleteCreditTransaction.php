<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\CreditTransaction;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateCreditTransactions;
use App\Actions\OrgAction;
use App\Models\Accounting\CreditTransaction;

class DeleteCreditTransaction extends OrgAction
{
    public function handle(CreditTransaction $creditTransaction): void
    {
        $customer = $creditTransaction->customer;
        $creditTransaction->delete();

        CustomerHydrateCreditTransactions::run($customer);
    }

    public function action(CreditTransaction $creditTransaction): void
    {
        $this->asAction = true;
        $this->initialisationFromShop($creditTransaction->shop, []);
        $this->handle($creditTransaction);
    }
}
