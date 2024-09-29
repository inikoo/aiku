<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 27 Sept 2024 20:30:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_has_models', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('model_type')->index();
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('model_id');
            $table->timestampsTz();
            $table->unique(['user_id', 'model_type', 'model_id']);
            $table->index(['model_type', 'model_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('user_has_models');
    }
};
