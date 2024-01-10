<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 12:27:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('type')->index();
            $table->string('name');
            $table->morphs('parent');
            $table->json('data');
            $table->json('compiled');
            $table->unsignedInteger('screenshot_id')->nullable();
            $table->foreign('screenshot_id')->references('id')->on('media');
            $table->boolean('is_seeded')->index()->default(false);
            $table->boolean('is_transactional')->index()->default(false);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
