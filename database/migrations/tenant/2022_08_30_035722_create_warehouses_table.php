<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:01:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('code')->index()->unique();
            $table->string('name');
            $table->jsonb('settings');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('source_id')->nullable()->unique();

        });
    }


    public function down()
    {
        Schema::dropIfExists('warehouses');
    }
};
