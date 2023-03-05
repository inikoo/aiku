<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 12:25:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('stored_items', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('code')->index();
            $table->boolean('status')->default('false')->comment('false for returned goods');
            $table->enum('state', ['booked','received','stored','returned']);
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('location_id')->nullable()->constrained();

            $table->string('notes');
            $table->boolean('return_requested');

            $table->timestampsTz();
            $table->dateTimeTz('received_at')->nullable();
            $table->dateTimeTz('stored_at')->nullable();
            $table->dateTimeTz('returned_at')->nullable();

            $table->softDeletesTz();
            $table->unsignedBigInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('stored_items');
    }
};
