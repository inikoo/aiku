<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 01 Sept 2022 19:01:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Dispatching\Picking\PickingOutcomeEnum;
use App\Enums\Dispatching\Picking\PickingStateEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    public function up(): void
    {
        Schema::create('pickings', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);
            $table->unsignedInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('delivery_note_id')->index();
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');
            $table->unsignedInteger('delivery_note_item_id')->index();
            $table->foreign('delivery_note_item_id')->references('id')->on('delivery_note_items');

            $table->boolean('status')->default(false)->index();

            $table->string('state')->default(PickingStateEnum::ASSIGNED->value)->index();
            $table->string('outcome')->default(PickingOutcomeEnum::HANDLING->value)->index();

            $table->decimal('quantity_required', 16, 3)->default(0);
            $table->decimal('quantity_picked', 16, 3)->nullable();
            $table->decimal('quantity_packed', 16, 3)->nullable();
            $table->decimal('quantity_dispatched', 16, 3)->nullable();

            $table->unsignedInteger('org_stock_movement_id')->nullable()->index();
            $table->foreign('org_stock_movement_id')->references('id')->on('org_stock_movements');

            $table->unsignedInteger('org_stock_id')->index();
            $table->foreign('org_stock_id')->references('id')->on('org_stocks');

            $table->unsignedSmallInteger('picker_id')->nullable()->index();
            $table->foreign('picker_id')->references('id')->on('users');

            $table->unsignedSmallInteger('packer_id')->nullable()->index();
            $table->foreign('packer_id')->references('id')->on('users');

            $table->string('vessel_picking')->default(null)->nullable()->index();
            $table->string('vessel_packing')->default(null)->nullable()->index();

            $table->unsignedSmallInteger('location_id')->nullable()->index();
            $table->foreign('location_id')->references('id')->on('locations');

            $table->jsonb('data');


            $table->dateTimeTz('picker_assigned_at')->nullable();
            $table->dateTimeTz('picking_at')->nullable();
            $table->dateTimeTz('picked_at')->nullable();
            $table->dateTimeTz('packer_assigned_at')->nullable();
            $table->dateTimeTz('packing_at')->nullable();
            $table->dateTimeTz('packed_at')->nullable();


            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('pickings');
    }
};
