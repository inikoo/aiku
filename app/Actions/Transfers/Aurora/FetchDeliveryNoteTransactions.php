<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 31 Jan 2023 20:16:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Dispatching\DeliveryNoteItem\StoreDeliveryNoteItem;
use App\Actions\Dispatching\DeliveryNoteItem\UpdateDeliveryNoteItem;
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


            if ($deliveryNoteItem = DeliveryNoteItem::where('source_id', $transactionData['delivery_note_item']['source_id'])->first()) {


                $deliveryNoteItem = UpdateDeliveryNoteItem::make()->action(
                    deliveryNoteItem: $deliveryNoteItem,
                    modelData: $transactionData['delivery_note_item'],
                    hydratorsDelay: 60,
                    strict: false
                );
            } else {


                $deliveryNoteItem = StoreDeliveryNoteItem::make()->action(
                    deliveryNote: $deliveryNote,
                    modelData:    $transactionData['delivery_note_item'],
                    hydratorsDelay: 60,
                    strict: false
                );

                $sourceData = explode(':', $deliveryNoteItem->source_id);
                DB::connection('aurora')->table('Inventory Transaction Fact')
                    ->where('Inventory Transaction Key', $sourceData[1])
                    ->update(['aiku_id' => $deliveryNoteItem->id]);


            }
            return $deliveryNoteItem;
        }


        return null;
    }
}
