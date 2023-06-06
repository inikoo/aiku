<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:36:20 Central European Summer Time, Benalmádena, Malaga Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique()->collation('und_ns');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->enum('state', ['construction', 'live', 'maintenance', 'closed'])->default('construction')->index();
            $table->string('code')->unique()->collation('und_ns_ci');
            $table->string('domain')->unique()->collation('und_ns_ci');
            $table->string('name')->unique()->collation('und_ns_ci_ai');
            $table->jsonb('settings');
            $table->jsonb('data');
            $table->jsonb('structure');
            $table->unsignedSmallInteger('current_layout_id')->index()->nullable();
            $table->timestampsTz();
            $table->timestampTz('launched_at')->nullable();
            $table->timestampTz('closed_at')->nullable();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
