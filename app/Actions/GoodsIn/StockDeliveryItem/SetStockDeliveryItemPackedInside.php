<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\GoodsIn\StockDeliveryItem;

use App\Actions\GoodsIn\StockDelivery\AddPurchaseOrdersToStockDelivery;
use App\Actions\OrgAction;
use App\Enums\GoodsIn\StockDeliveryItem\StockDeliveryItemStateEnum;
use App\Http\Resources\Procurement\StockDeliveryItemResource;
use App\Models\GoodsIn\StockDelivery;
use App\Models\GoodsIn\StockDeliveryItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * An agent packs a product inside another product of the same container (small boxes inside a big one): the line
 * takes no space of its own and says where it is. One level only, so nothing is packed inside a line that is
 * itself packed inside another.
 */
class SetStockDeliveryItemPackedInside extends OrgAction
{
    /**
     * True for a line (aliased sdi) packed inside a line still in the container: it takes no space of its own.
     */
    public const string IS_PACKED_INSIDE_SQL = "exists (select 1 from stock_delivery_items as outer_item where outer_item.id = sdi.packed_inside_stock_delivery_item_id and outer_item.deleted_at is null and outer_item.state not in ('cancelled', 'not_received'))";

    private StockDeliveryItem $stockDeliveryItem;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return AddPurchaseOrdersToStockDelivery::userCanAdd($this->stockDeliveryItem->stockDelivery, $request->user());
    }

    /**
     * The lines still in the container with their code, so a page or a document can name what each line is packed
     * inside and what is packed inside it.
     *
     * @return Collection<int, object{id: int, code: string|null, packed_inside_stock_delivery_item_id: int|null}>
     */
    public static function containerLines(StockDelivery $stockDelivery): Collection
    {
        return DB::table('stock_delivery_items as sdi')
            ->leftJoin('supplier_products as sp', 'sp.id', '=', 'sdi.supplier_product_id')
            ->leftJoin('org_stocks as os', 'os.id', '=', 'sdi.org_stock_id')
            ->where('sdi.stock_delivery_id', $stockDelivery->id)
            ->whereNull('sdi.deleted_at')
            ->whereNotIn('sdi.state', [StockDeliveryItemStateEnum::CANCELLED->value, StockDeliveryItemStateEnum::NOT_RECEIVED->value])
            ->orderByRaw('coalesce(sp.code, os.code)')
            ->selectRaw('sdi.id, coalesce(sp.code, os.code) as code, sdi.packed_inside_stock_delivery_item_id')
            ->get();
    }

    /**
     * The checks run again under a lock on the delivery, so two lines packed inside each other at the same moment
     * can not both pass.
     *
     * @throws ValidationException
     */
    public function handle(StockDeliveryItem $stockDeliveryItem, array $modelData): StockDeliveryItem
    {
        $outerItemId = $modelData['packed_inside_stock_delivery_item_id'];

        DB::transaction(function () use ($stockDeliveryItem, $outerItemId) {
            StockDelivery::whereKey($stockDeliveryItem->stock_delivery_id)->lockForUpdate()->first();

            if ($outerItemId !== null && $error = $this->packingError($stockDeliveryItem, (int) $outerItemId)) {
                throw ValidationException::withMessages(['packed_inside_stock_delivery_item_id' => $error]);
            }

            $stockDeliveryItem->update(['packed_inside_stock_delivery_item_id' => $outerItemId]);
        });

        return $stockDeliveryItem;
    }

    private function packingError(StockDeliveryItem $stockDeliveryItem, int $outerItemId): ?string
    {
        $outerItem = StockDeliveryItem::where('stock_delivery_id', $stockDeliveryItem->stock_delivery_id)
            ->whereNull('deleted_at')
            ->whereNotIn('state', [StockDeliveryItemStateEnum::CANCELLED, StockDeliveryItemStateEnum::NOT_RECEIVED])
            ->find($outerItemId);

        return match (true) {
            !$outerItem || $outerItem->id === $stockDeliveryItem->id => __('Choose another product of this container'),
            (bool) $outerItem->packed_inside_stock_delivery_item_id  => __('That product is itself packed inside another product'),
            $stockDeliveryItem->packedItems()->exists()              => __('Other products are packed inside this one, unpack them first'),
            default                                                   => null,
        };
    }

    public function rules(): array
    {
        return [
            'packed_inside_stock_delivery_item_id' => ['present', 'nullable', 'integer', 'min:1'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!SetStockDeliveryItemCbmInContainer::canBeEdited($this->stockDeliveryItem)) {
            $validator->errors()->add('packed_inside_stock_delivery_item_id', __('What a product is packed inside can only be changed before the container is dispatched'));
        }
    }

    public function asController(StockDeliveryItem $stockDeliveryItem, ActionRequest $request): StockDeliveryItem
    {
        $this->stockDeliveryItem = $stockDeliveryItem;
        $this->initialisation($stockDeliveryItem->organisation, $request);

        return $this->handle($stockDeliveryItem, $this->validatedData);
    }

    public function action(StockDeliveryItem $stockDeliveryItem, array $modelData): StockDeliveryItem
    {
        $this->asAction          = true;
        $this->stockDeliveryItem = $stockDeliveryItem;
        $this->initialisation($stockDeliveryItem->organisation, $modelData);

        return $this->handle($stockDeliveryItem, $this->validatedData);
    }

    public function jsonResponse(StockDeliveryItem $stockDeliveryItem): StockDeliveryItemResource
    {
        return new StockDeliveryItemResource($stockDeliveryItem);
    }
}
