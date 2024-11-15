<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 14 Nov 2024 17:16:56 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('model_has_ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id');
            $table->string('model_type');
            $table->index(['model_id', 'model_type']);
            $table->unsignedSmallInteger('ingredient_id')->index();
            $table->foreign('ingredient_id')->references('id')->on('ingredients');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('model_has_ingredients');
    }
};
