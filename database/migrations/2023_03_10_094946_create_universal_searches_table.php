<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Nov 2023 23:23:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('universal_searches', function (Blueprint $table) {
            $table->id();
            $table->jsonb('organisations');
            $table->nullableMorphs('model');
            $table->string('section')->nullable();
            $table->string('title');
            $table->string('description')->nullable();
            $table->timestampsTz();
            $table->unique(['model_id','model_type']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('universal_searches');
    }
};
