<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 13:31:15 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->unsignedInteger('provider_id')->index();
            $table->string('provider_type');
            $table->string('number');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->unique(['provider_id', 'provider_type']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
