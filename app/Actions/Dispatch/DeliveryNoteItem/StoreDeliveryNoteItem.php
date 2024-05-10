<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\DeliveryNoteItem;

use App\Actions\OrgAction;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\DeliveryNoteItem;
use App\Models\Inventory\OrgStock;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreDeliveryNoteItem extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public function handle(DeliveryNote $deliveryNote, array $modelData): DeliveryNoteItem
    {

        data_set($modelData, 'group_id', $deliveryNote->group_id);
        data_set($modelData, 'organisation_id', $deliveryNote->organisation_id);


        $orgStock = OrgStock::find($modelData['org_stock_id']);
        data_set($modelData, 'stock_id', $orgStock->stock_id);


        data_set($modelData, 'stock_family_id', $orgStock->stock->stock_family_id);
        data_set($modelData, 'org_stock_family_id', $orgStock->org_stock_family_id);


        /** @var DeliveryNoteItem $deliveryNoteItem */
        $deliveryNoteItem = $deliveryNote->deliveryNoteItems()->create($modelData);

        return $deliveryNoteItem;
    }

    public function rules(): array
    {
        $rules= [
            'org_stock_id'          => ['required', 'exists:org_stocks,id'],
            'transaction_id'        => ['required', 'exists:transactions,id']
        ];

        if(!$this->strict) {
            $rules['transaction_id'] = ['sometimes', 'nullable', 'exists:transactions,id'];
        }

        return $rules;

    }

    public function action(DeliveryNote $deliveryNote, array $modelData, $strict=true): DeliveryNoteItem
    {

        $this->strict = $strict;
        $this->initialisation($deliveryNote->organisation, $modelData);


        return $this->handle($deliveryNote, $this->validatedData);
    }
}
