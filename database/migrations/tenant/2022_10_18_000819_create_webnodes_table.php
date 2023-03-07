<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 13:17:58 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('webnodes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->enum('type', ['structural', 'content'])->index();
            $table->string('locus')->unique()->nullable()->comment('for structural type, identification od the node');
            $table->unsignedSmallInteger('website_id')->index();
            $table->foreign('website_id')->references('id')->on('websites');
            $table->timestampsTz();
        });
    }

    public function down()
    {
        Schema::dropIfExists('webnodes');
    }
};
