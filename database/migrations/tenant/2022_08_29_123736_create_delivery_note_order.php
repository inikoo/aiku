<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 21:21:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('delivery_note_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('delivery_note_id')->constrained();
            $table->timestampsTz();
            $table->unique(
                [
                    'order_id',
                    'delivery_note_id'
                ]
            );
        });
    }


    public function down()
    {
        Schema::dropIfExists('delivery_note_order');
    }
};
