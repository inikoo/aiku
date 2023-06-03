<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 18:35:32 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique()->collation('und_ns');
            $table->unsignedBigInteger('image_id')->nullable();
            $table->unsignedSmallInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('parent_id');
            $table->string('parent_type');
            $table->string('type')->index();
            $table->boolean('is_family')->default(false);
            $table->string('state')->nullable()->index();
            $table->string('code')->index()->collation('und_ns');
            $table->string('name', 255)->nullable()->collation('und_ns_ci_ai');
            $table->text('description')->nullable()->collation('und_ns_ci_ai');
            $table->jsonb('data');
            $table->timestampstz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_department_id')->nullable()->unique();
            $table->unsignedInteger('source_family_id')->nullable()->unique();
            $table->index(['parent_id','parent_type']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
