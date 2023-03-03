<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 12:29:33 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * App\Models\Central\TenantProcurementStats
 *
 * @property int $id
 * @property string $tenant_id
 * @property int $number_suppliers
 * @property int $number_active_suppliers
 * @property int $number_agents
 * @property int $number_active_agents
 * @property int $number_active_tenant_agents
 * @property int $number_active_global_agents
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
 * @method static Builder|TenantProcurementStats newModelQuery()
 * @method static Builder|TenantProcurementStats newQuery()
 * @method static Builder|TenantProcurementStats query()
 * @method static Builder|TenantProcurementStats whereCreatedAt($value)
 * @method static Builder|TenantProcurementStats whereId($value)
 * @method static Builder|TenantProcurementStats whereNumberActiveAgents($value)
 * @method static Builder|TenantProcurementStats whereNumberActiveGlobalAgents($value)
 * @method static Builder|TenantProcurementStats whereNumberActiveSuppliers($value)
 * @method static Builder|TenantProcurementStats whereNumberActiveTenantAgents($value)
 * @method static Builder|TenantProcurementStats whereNumberAgents($value)
 * @method static Builder|TenantProcurementStats whereNumberDeliveries($value)
 * @method static Builder|TenantProcurementStats whereNumberPurchaseOrders($value)
 * @method static Builder|TenantProcurementStats whereNumberPurchaseOrdersStateCancelled($value)
 * @method static Builder|TenantProcurementStats whereNumberPurchaseOrdersStateConfirmed($value)
 * @method static Builder|TenantProcurementStats whereNumberPurchaseOrdersStateDelivered($value)
 * @method static Builder|TenantProcurementStats whereNumberPurchaseOrdersStateDispatched($value)
 * @method static Builder|TenantProcurementStats whereNumberPurchaseOrdersStateInProcess($value)
 * @method static Builder|TenantProcurementStats whereNumberPurchaseOrdersStateSubmitted($value)
 * @method static Builder|TenantProcurementStats whereNumberSuppliers($value)
 * @method static Builder|TenantProcurementStats whereNumberWorkshops($value)
 * @method static Builder|TenantProcurementStats whereTenantId($value)
 * @method static Builder|TenantProcurementStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TenantProcurementStats extends Model
{
    protected $table = 'tenant_procurement_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
