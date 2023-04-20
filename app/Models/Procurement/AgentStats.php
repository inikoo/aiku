<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 12:07:51 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Procurement\AgentStats
 *
 * @property int $id
 * @property int $agent_id
 * @property int $number_suppliers
 * @property int $number_active_suppliers
 * @property int $number_products all excluding discontinued
 * @property int $number_products_state_creating
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property int $number_products_stock_quantity_status_excess
 * @property int $number_products_stock_quantity_status_ideal
 * @property int $number_products_stock_quantity_status_low
 * @property int $number_products_stock_quantity_status_critical
 * @property int $number_products_stock_quantity_status_out_of_stock
 * @property int $number_products_stock_quantity_status_no_applicable
 * @property int $number_purchase_orders
 * @property int $number_purchase_orders_state_in_process
 * @property int $number_purchase_orders_state_submitted
 * @property int $number_purchase_orders_state_confirmed
 * @property int $number_purchase_orders_state_dispatched
 * @property int $number_purchase_orders_state_delivered
 * @property int $number_purchase_orders_state_cancelled
 * @property int $number_deliveries
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Procurement\Agent $agent
 * @method static Builder|AgentStats newModelQuery()
 * @method static Builder|AgentStats newQuery()
 * @method static Builder|AgentStats query()
 * @mixin \Eloquent
 */
class AgentStats extends Model
{
    use UsesLandlordConnection;

    protected $table = 'agent_stats';

    protected $guarded = [];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
