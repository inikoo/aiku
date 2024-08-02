<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Jul 2024 19:55:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\FulfilmentTransaction\SetClausesInFulfilmentTransaction;
use App\Actions\OrgAction;
use App\Models\Fulfilment\PalletReturn;

class UpdatePalletReturnFulfilmentTransactionClause extends OrgAction
{
    public function handle(PalletReturn $palletReturn)
    {
        foreach ($palletReturn->transactions as $transaction)
        {
            SetClausesInFulfilmentTransaction::run($transaction);
        }
        $palletReturn->refresh();

        return $palletReturn;
    }
}