<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 14:36:52 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('web_block_types', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('scope')->index();
            $table->string('slug')->unique()->index();
            $table->string('code');
            $table->string('name');
            $table->string('class')->index();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('web_block_types');
    }
};
