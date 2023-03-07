<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 14:20:03 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('webpages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('code')->index();
            $webpageTypes = ['storefront', 'product', 'category', 'shopping-cart', 'checkout', 'store-info', 'engagement'];
            $table->enum('type', $webpageTypes)->index();
            $table->unsignedInteger('webnode_id');
            $table->foreign('webnode_id')->references('id')->on('webnodes')->onUpdate('cascade')->onDelete('cascade');

            $table->jsonb('components');
            $table->timestampsTz();
        });

        Schema::table('webnodes', function (Blueprint $table) {
            $table->unsignedInteger('main_webpage_id')->index()->nullable();
            $table->foreign('main_webpage_id')->references('id')->on('webpages');
        });
    }

    public function down()
    {
        Schema::table('webnodes', function (Blueprint $table) {
            $table->dropColumn('main_webpage_id');
        });
        Schema::dropIfExists('webpages');
    }
};
