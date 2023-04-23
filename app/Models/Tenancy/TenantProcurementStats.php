<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:22 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Tenancy\TenantProcurementStats
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $number_suppliers
 * @property int $number_active_suppliers
 * @property int $number_agents
 * @property int $number_active_agents
 * @property int $number_active_tenant_agents
 * @property int $number_active_global_agents
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
 * @property int $number_workshops
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tenancy\Tenant $tenant
 * @method static Builder|TenantProcurementStats newModelQuery()
 * @method static Builder|TenantProcurementStats newQuery()
 * @method static Builder|TenantProcurementStats query()
 * @mixin \Eloquent
 */
class TenantProcurementStats extends Model
{
    use UsesLandlordConnection;

    protected $table = 'tenant_procurement_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
