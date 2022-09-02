<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 01 Sept 2022 18:58:57 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained();
            $table->unsignedBigInteger('delivery_note_id')->index();
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');
            $table->foreignId('order_id')->nullable()->constrained();
            $table->foreignId('historic_product_id')->nullable()->constrained();
            $table->decimal('quantity', 16, 3)->nullable();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_note_items');
    }
};
