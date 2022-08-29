<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 13:38:35 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('code')->unique()->index();
            $table->string('name')->index();
            $table->string('symbol');

            $table->smallInteger('fraction_digits');
            $table->string('status')->nullable()->index();
            $table->jsonb('data');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('currencies');
    }
};
