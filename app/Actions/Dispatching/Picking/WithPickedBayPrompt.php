<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 22 Oct 2026 17:20:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Picking;

use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Models\Dispatching\DeliveryNoteItem;
use App\Models\Inventory\PickedBay;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

/**
 * HELP-4229: whoever replenishes a waiting line has to take it to the bay where the rest of the
 * order was left, so once the line is complete the waiting items page asks them to confirm that
 * bay or move the order to another one.
 */
trait WithPickedBayPrompt
{
    /**
     * @return array{delivery_note: array{id: int, slug: string, reference: string, is_ready: bool}, org_stock_code: ?string, picked_bays: array<int, array{id: int, code: string}>}|null
     */
    public static function pickedBayPrompt(DeliveryNoteItem $deliveryNoteItem): ?array
    {
        $deliveryNoteItem->refresh();

        if ((float) $deliveryNoteItem->quantity_waiting_warehouse > 0) {
            return null;
        }

        $deliveryNote = $deliveryNoteItem->deliveryNote;

        return [
            'delivery_note'  => [
                'id'        => $deliveryNote->id,
                'slug'      => $deliveryNote->slug,
                'reference' => $deliveryNote->reference,
                'is_ready'  => $deliveryNote->state !== DeliveryNoteStateEnum::HANDLING_BLOCKED,
            ],
            'org_stock_code' => $deliveryNoteItem->orgStock?->code,
            'picked_bays'    => $deliveryNote->pickedBays()->orderBy('code')->get()
                ->map(fn (PickedBay $pickedBay) => ['id' => $pickedBay->id, 'code' => $pickedBay->code])
                ->all(),
        ];
    }

    protected function flashPickedBayPrompt(DeliveryNoteItem $deliveryNoteItem): void
    {
        if ($prompt = static::pickedBayPrompt($deliveryNoteItem)) {
            Inertia::flash('picked_bay_prompt', $prompt);
        }
    }

    /** Inertia only carries flash data across an explicit redirect, not the one it makes for an empty response. */
    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
