<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:18:11 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('universal_searches', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('model');
            $table->string('section')->nullable();
            $table->jsonb('route')->nullable();
            $table->string('icon')->nullable();
            $table->string('primary_term');
            $table->string('secondary_term')->nullable();
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('universal_searches');
    }
};
