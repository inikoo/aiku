<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 01 Sept 2022 18:58:57 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Dispatch\DeliveryNoteItem\DeliveryNoteItemStateEnum;
use App\Enums\Dispatch\DeliveryNoteItem\DeliveryNoteItemStatusEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    public function up(): void
    {
        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->increments('id');
            $table=$this->groupOrgRelationship($table);
            $table->unsignedInteger('delivery_note_id')->index();
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');

            $table->unsignedInteger('stock_family_id')->index();
            $table->foreign('stock_family_id')->references('id')->on('stock_families');

            $table->unsignedInteger('stock_id')->index();
            $table->foreign('stock_id')->references('id')->on('stocks');

            $table->unsignedInteger('org_stock_family_id')->index();
            $table->foreign('org_stock_family_id')->references('id')->on('org_stock_families');

            $table->unsignedInteger('org_stock_id')->index();
            $table->foreign('org_stock_id')->references('id')->on('org_stocks');


            $table->unsignedInteger('transaction_id')->index()->nullable();
            $table->foreign('transaction_id')->references('id')->on('transactions');

            $table->unsignedInteger('picking_id')->nullable()->index();
            $table->foreign('picking_id')->references('id')->on('pickings');

            $table->string('state')->default(DeliveryNoteItemStateEnum::ON_HOLD->value)->index();
            $table->string('status')->default(DeliveryNoteItemStatusEnum::HANDLING->value)->index();

            $table->decimal('quantity_required', 16, 3)->default(0);
            $table->decimal('quantity_picked', 16, 3)->nullable();
            $table->decimal('quantity_packed', 16, 3)->nullable();
            $table->decimal('quantity_dispatched', 16, 3)->nullable();

            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
    }
};
