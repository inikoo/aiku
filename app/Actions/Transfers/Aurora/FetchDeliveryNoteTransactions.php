<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 31 Jan 2023 20:16:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Dispatching\DeliveryNoteItem\StoreDeliveryNoteItem;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Dispatching\DeliveryNoteItem;
use App\Transfers\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchDeliveryNoteTransactions
{
    use AsAction;


    public function handle(SourceOrganisationService $organisationSource, int $source_id, DeliveryNote $deliveryNote): ?DeliveryNoteItem
    {
        if ($transactionData = $organisationSource->fetchDeliveryNoteTransaction(id: $source_id, deliveryNote: $deliveryNote)) {
            if (!DeliveryNoteItem::where('source_id', $transactionData['delivery_note_item']['source_id'])
                ->first()) {
                $transaction = StoreDeliveryNoteItem::make()->action(
                    deliveryNote: $deliveryNote,
                    modelData:    $transactionData['delivery_note_item'],
                    strict: false
                );

                DB::connection('aurora')->table('Inventory Transaction Fact')
                    ->where('Inventory Transaction Key', $transaction->source_id)
                    ->update(['aiku_id' => $transaction->id]);

                return $transaction;
            }
        }


        return null;
    }
}
