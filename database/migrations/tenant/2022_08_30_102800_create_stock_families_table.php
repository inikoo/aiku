<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 09:52:34 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('stock_families', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique();

            $table->string('code')->index();

            $stockFamilyStates=['in-process', 'active','discontinuing', 'discontinued'];
            $table->enum('state',$stockFamilyStates )->nullable()->index();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->jsonb('data');
            $table->timestampstz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('stock_families');
    }
};
