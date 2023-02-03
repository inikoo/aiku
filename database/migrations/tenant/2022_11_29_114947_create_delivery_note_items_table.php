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

    public function up()
    {
        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('delivery_note_id')->constrained();
            $table->foreignId('stock_id')->nullable()->constrained();

            $table->foreignId('transaction_id')->nullable()->constrained();
            $table->foreignId('picking_id')->nullable()->constrained();

            $table->enum(
                'state',
                [
                    'on-hold',
                    'picking',
                    'picked',
                    'packed',
                    'dispatched',
                    'fail',
                    'cancelled'
                ]
            )->index();
            $table->enum('status',['in-process','done','done-with-missing','fail']);



            $table->decimal('required', 16, 3);
            $table->decimal('quantity', 16, 3)->default(0);
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('delivery_note_items');
    }
};
