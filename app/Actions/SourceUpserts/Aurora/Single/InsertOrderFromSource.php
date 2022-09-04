<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 13:37:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\SourceUpserts\Aurora\Single;

use App\Actions\Sales\Order\StoreOrder;
use App\Models\Sales\Order;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
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

                $this->fetchTransactions($organisationSource, $order);
            }
            else{
                $res   = StoreOrder::run($orderData['parent'], $orderData['order'], $orderData['billing_address'], $orderData['delivery_address']);
                $order = $res->model;
                $this->fetchTransactions($organisationSource, $order);

                return $order;
            }
        }

        return null;
    }

    private function fetchTransactions($organisationSource, $order): void
    {
        foreach (
            DB::connection('aurora')
                ->table('Order Transaction Fact')
                ->select('Order Transaction Fact Key')
                ->where('Order Key', $order->organisation_source_id)
                ->get() as $auroraData
        ) {
            InsertTransactionFromSource::run($organisationSource, $auroraData->{'Order Transaction Fact Key'},$order);
        }
    }


}
