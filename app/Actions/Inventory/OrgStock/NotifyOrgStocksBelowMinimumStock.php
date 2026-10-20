<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 19 Oct 2026 16:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock;

use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateOrgStocksBelowMinimumStock;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Models\Inventory\OrgStock;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\User;
use App\Notifications\OrgStocksBelowMinimumStockNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Permission;

/**
 * Tells the stock supervisors of the organisation's warehouses and its procurement supervisors when SKOs
 * fall below their minimum stock. Each SKO is told once per drop: minimum_stock_alerted_at is claimed when
 * it goes below and cleared when it is restocked, its minimum is cleared or lowered, or it stops being active.
 */
class NotifyOrgStocksBelowMinimumStock
{
    use AsAction;

    public function handle(Warehouse $warehouse): int
    {
        $organisationId = $warehouse->organisation_id;

        OrgStock::where('organisation_id', $organisationId)
            ->whereNotNull('minimum_stock_alerted_at')
            ->where(function ($query) {
                $query->whereNull('minimum_stock')
                    ->orWhere('state', '!=', OrgStockStateEnum::ACTIVE)
                    ->orWhereColumn('quantity_in_locations', '>=', 'minimum_stock');
            })
            ->update(['minimum_stock_alerted_at' => null]);

        $orgStockIds = WarehouseHydrateOrgStocksBelowMinimumStock::belowMinimumStock(OrgStock::query(), $organisationId)
            ->whereNull('org_stocks.minimum_stock_alerted_at')
            ->pluck('org_stocks.id');

        if ($orgStockIds->isEmpty()) {
            return 0;
        }

        $claimedIds = collect(DB::select(
            'update org_stocks set minimum_stock_alerted_at = now() where minimum_stock_alerted_at is null and id in ('.$orgStockIds->map(fn () => '?')->implode(',').') returning id',
            $orgStockIds->all()
        ))->pluck('id');

        if ($claimedIds->isEmpty()) {
            return 0;
        }

        $recipients = $this->recipients($warehouse);
        if ($recipients->isEmpty()) {
            return 0;
        }

        $codes = OrgStock::whereIn('id', $claimedIds)
            ->orderByRaw('minimum_stock - quantity_in_locations desc')
            ->orderBy('code')
            ->pluck('code')
            ->all();

        Notification::send($recipients, new OrgStocksBelowMinimumStockNotification($warehouse, $codes));

        return $recipients->count();
    }

    /**
     * Read through the tables rather than User::permission(): roles are scoped to the group, and this runs in a
     * queue worker where no permissions team is set.
     *
     * @return Collection<int, User>
     */
    private function recipients(Warehouse $warehouse): Collection
    {
        $permissionNames = $warehouse->organisation->warehouses()->pluck('id')
            ->map(fn (int $warehouseId) => "supervisor-stocks.$warehouseId")
            ->push("org-supervisor.$warehouse->organisation_id.procurement");

        $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id');

        if ($permissionIds->isEmpty()) {
            return collect();
        }

        $throughRoles = DB::table('model_has_roles')
            ->join('role_has_permissions', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', 'User')
            ->where('model_has_roles.group_id', $warehouse->group_id)
            ->whereIn('role_has_permissions.permission_id', $permissionIds)
            ->select('model_has_roles.model_id');

        $userIds = DB::table('model_has_permissions')
            ->where('model_type', 'User')
            ->where('group_id', $warehouse->group_id)
            ->whereIn('permission_id', $permissionIds)
            ->select('model_id')
            ->union($throughRoles)
            ->pluck('model_id');

        return User::whereIn('id', $userIds)->where('status', true)->get();
    }
}
