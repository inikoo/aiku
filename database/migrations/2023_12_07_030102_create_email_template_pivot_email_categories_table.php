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
        Schema::create('email_template_pivot_email_categories', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('email_template_id');
            $table->foreign('email_template_id')->references('id')->on('email_templates')->onDelete('cascade');
            $table->unsignedSmallInteger('email_template_category_id');
            $table->foreign('email_template_category_id')->references('id')->on('email_template_categories')->onDelete('cascade');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('email_template_pivot_email_categories');
    }
};
