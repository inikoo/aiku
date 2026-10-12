<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 23 Sep 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\SysAdmin\McpChange;

use App\Enums\EnumHelperTrait;

enum McpChangeTypeEnum: string
{
    use EnumHelperTrait;

    case RELATED_PRODUCTS = 'related_products';
    case ORG_STOCK_STATE = 'org_stock_state';
    case PARTNER_SHOPPING_LIST = 'partner_shopping_list';
    case PRODUCTION_RECORD = 'production_record';
    case PRODUCTION_RECIPE = 'production_recipe';
    case PLACED_ORDER = 'placed_order';
    case MASTER_PRICES = 'master_prices';
    case STOCK_DELIVERY_COSTS = 'stock_delivery_costs';
    case CUSTOMER_SERVICE = 'customer_service';

    public static function labels(): array
    {
        return [
            'related_products' => __('Related products'),
            'org_stock_state'       => __('SKO state'),
            'partner_shopping_list' => __('Hub shopping list'),
            'production_record'     => __('Artefact, raw material or task'),
            'production_recipe'     => __('Artefact recipe'),
            'placed_order'          => __('Order placed'),
            'master_prices'         => __('Master prices'),
            'stock_delivery_costs'  => __('Stock delivery costs'),
            'customer_service'      => __('Customer service'),
        ];
    }

    /**
     * The user switch that lets someone make, and therefore also undo, this kind of change.
     */
    public function userSwitch(): string
    {
        return match ($this) {
            McpChangeTypeEnum::RELATED_PRODUCTS => 'can_use_mcp_web',
            McpChangeTypeEnum::ORG_STOCK_STATE  => 'can_use_mcp_discontinue',
            McpChangeTypeEnum::PARTNER_SHOPPING_LIST => 'can_use_mcp_procurement',
            McpChangeTypeEnum::PRODUCTION_RECORD, McpChangeTypeEnum::PRODUCTION_RECIPE => 'can_use_mcp_production',
            McpChangeTypeEnum::PLACED_ORDER => 'can_use_mcp_place_orders',
            McpChangeTypeEnum::MASTER_PRICES => 'can_use_mcp_prices',
            McpChangeTypeEnum::STOCK_DELIVERY_COSTS => 'can_use_mcp_costing',
            McpChangeTypeEnum::CUSTOMER_SERVICE => 'can_use_mcp_customer_service',
        };
    }

    /**
     * An order sent to a supplier, a reply emailed to a customer or a credit on their balance has
     * already left the building: it is undone by a person, with the other party, never from the log.
     */
    public function canBeReverted(): bool
    {
        return !in_array($this, [McpChangeTypeEnum::PLACED_ORDER, McpChangeTypeEnum::CUSTOMER_SERVICE]);
    }
}
