<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 27 Sep 2023 19:05:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('organisation_user_id')->nullable();
            $table->foreign('organisation_user_id')->references('id')->on('organisation_users');
            $table->string('type');
            $table->string('original_filename');
            $table->string('filename');
            $table->string('path');
            $table->unsignedInteger('number_rows')->default(0);
            $table->unsignedInteger('number_success')->default(0);
            $table->unsignedInteger('number_fails')->default(0);
            $table->dateTimeTz('uploaded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
