<?php

use App\Actions\Inventory\WarehouseArea\UpdateWarehouseArea;
use Illuminate\Support\Facades\Route;

// Route::patch('/agent/{agent:id}', UpdateAgent::class)->name('agent.update');
Route::patch('/warehouse/{warehouseArea:id}/areas', UpdateWarehouseArea::class)->name('warehouse-area.update');
