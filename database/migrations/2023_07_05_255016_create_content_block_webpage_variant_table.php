<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 13 Jul 2023 14:24:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('content_block_webpage_variant', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedInteger('webpage_variant_id')->index();
            $table->foreign('webpage_variant_id')->references('id')->on('webpage_variants')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('content_block_id')->index();
            $table->foreign('content_block_id')->references('id')->on('content_blocks')->onUpdate('cascade')->onDelete('cascade');
            $table->smallInteger('position')->default(0);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('content_block_webpage_variant');
    }
};
