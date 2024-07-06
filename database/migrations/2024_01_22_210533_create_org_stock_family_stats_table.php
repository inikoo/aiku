<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 15:20:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Inventory\OrgStock\OrgStockQuantityStatusEnum;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;

    public function up(): void
    {
        Schema::create('org_stock_family_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table = $this->groupOrgRelationship($table);
            $table->unsignedInteger('org_stock_family_id')->index();
            $table->foreign('org_stock_family_id')->references('id')->on('org_stock_families');
            $table->unsignedInteger('number_org_stocks')->default(0);
            foreach (OrgStockStateEnum::cases() as $stockState) {
                $table->unsignedInteger('number_org_stocks_state_'.$stockState->snake())->default(0);
            }
            foreach (OrgStockQuantityStatusEnum::cases() as $quantityStatus) {
                $table->unsignedInteger('number_org_stocks_quantity_status_'.$quantityStatus->snake())->default(0);
            }
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('org_stock_family_stats');
    }
};
