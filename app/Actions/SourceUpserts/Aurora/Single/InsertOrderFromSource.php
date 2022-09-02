<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 13:37:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\SourceUpserts\Aurora\Single;

use App\Actions\Sales\Order\StoreOrder;
use App\Actions\SourceUpserts\Aurora\Mass\InsertTransactionsFromSource;
use App\Models\Sales\Order;
use App\Services\Organisation\SourceOrganisationService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class InsertOrderFromSource
{
    use AsAction;
    use WithSingleFromSourceCommand;

    public string $commandSignature = 'source-update:order {organisation_code} {organisation_source_id}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?Order
    {
        if ($orderData = $organisationSource->fetchOrder($organisation_source_id)) {
            if ($order=Order::where('organisation_source_id', $orderData['order']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {
                print "duplicated\n";
                InsertTransactionsFromSource::run($organisationSource, $order);
            }
            else{
                $res   = StoreOrder::run($orderData['parent'], $orderData['order'], $orderData['billing_address'], $orderData['delivery_address']);
                $order = $res->model;
                InsertTransactionsFromSource::run($organisationSource, $order);

                return $order;
            }
        }

        return null;
    }


}
