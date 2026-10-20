<?php

/*
 * Author Louis Perez
 * Created on 20-10-2026-09h-30m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Actions\Goods\ProductDevelopment\ProductDevelopmentAccess;
use App\Actions\Goods\ProductDevelopment\ProductDevelopmentPurchaseOrders;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStatusEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentTypeEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\TradeUnit;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Goods\TradeUnitFamily;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\User;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * The developments shown from elsewhere in Aiku (a trade unit, a family, a supplier, an agent, a purchase
 * order), limited to the ones the viewer may open.
 */
class GetRelatedProductDevelopments
{
    use AsAction;

    public const int LIMIT = 50;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forTradeUnit(TradeUnit $tradeUnit, User $viewer): array
    {
        return $this->rows(
            ProductDevelopment::query()->whereHas('items', fn (Builder $items) => $items->where('trade_unit_id', $tradeUnit->id)),
            $viewer
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forTradeUnitFamily(TradeUnitFamily $tradeUnitFamily, User $viewer): array
    {
        return $this->rows(
            ProductDevelopment::query()->where(fn (Builder $query) => $query
                ->where('trade_unit_family_id', $tradeUnitFamily->id)
                ->orWhereHas('items.tradeUnit', fn (Builder $tradeUnits) => $tradeUnits->where('trade_unit_family_id', $tradeUnitFamily->id))),
            $viewer
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forSupplier(Supplier $supplier, User $viewer, bool $openOnly = true): array
    {
        return $this->rows(ProductDevelopment::query()->where('supplier_id', $supplier->id), $viewer, $openOnly);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forAgent(Agent $agent, User $viewer, bool $openOnly = true): array
    {
        return $this->rows(
            ProductDevelopment::query()->where(fn (Builder $query) => $query
                ->where('agent_id', $agent->id)
                ->orWhereHas('supplier', fn (Builder $suppliers) => $suppliers->where('agent_id', $agent->id))),
            $viewer,
            $openOnly
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forPurchaseOrder(PurchaseOrder $purchaseOrder, User $viewer): array
    {
        $links = ProductDevelopmentPurchaseOrders::developmentLinks($purchaseOrder);
        if ($links === []) {
            return [];
        }

        return array_map(
            fn (array $row) => [...$row, 'linked' => $links[$row['id']] ?? null],
            $this->rows(ProductDevelopment::query()->whereIn('id', array_keys($links)), $viewer)
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rows(Builder $query, User $viewer, bool $openOnly = false): array
    {
        $stageLabels = ProductDevelopmentStageEnum::positionLabels();

        return (new ProductDevelopmentAccess())->restrictToVisible($query, $viewer)
            ->when($openOnly, fn (Builder $query) => $query->whereIn('status', [ProductDevelopmentStatusEnum::ACTIVE->value, ProductDevelopmentStatusEnum::ON_HOLD->value]))
            ->with(['owner', 'stats'])
            ->orderByDesc('id')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (ProductDevelopment $productDevelopment) => [
                'id'                 => $productDevelopment->id,
                'code'               => $productDevelopment->code,
                'name'               => $productDevelopment->name,
                'type'               => [
                    'label' => ProductDevelopmentTypeEnum::labels()[$productDevelopment->type->value],
                    'icon'  => $productDevelopment->type->icon(),
                    'color' => $productDevelopment->type->color(),
                ],
                'status'             => [
                    'label' => ProductDevelopmentStatusEnum::labels()[$productDevelopment->status->value],
                    'icon'  => $productDevelopment->status->icon(),
                    'color' => $productDevelopment->status->color(),
                ],
                'stage'              => $stageLabels[$productDevelopment->current_position] ?? null,
                'missing'            => (int) $productDevelopment->stats?->number_required_missing,
                'owner'              => $productDevelopment->owner?->contact_name ?: $productDevelopment->owner?->username,
                'target_online_date' => $productDevelopment->target_online_date?->toDateString(),
                'route'              => ['name' => 'grp.product_developments.show', 'parameters' => ['productDevelopment' => $productDevelopment->slug]],
            ])
            ->all();
    }

    public function count(Builder $query, User $viewer): int
    {
        return (new ProductDevelopmentAccess())->restrictToVisible($query, $viewer)->count();
    }
}
