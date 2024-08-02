<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Jul 2024 19:55:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\FulfilmentTransaction\SetClausesInFulfilmentTransaction;
use App\Actions\OrgAction;
use App\Models\Fulfilment\PalletDelivery;

class UpdatePalletDeliveryFulfilmentTransactionClause extends OrgAction
{
    public function handle(PalletDelivery $palletDelivery)
    {
        foreach ($palletDelivery->transactions as $transaction)
        {
            SetClausesInFulfilmentTransaction::run($transaction);
        }
        $palletDelivery->refresh();

        return $palletDelivery;
    }
}