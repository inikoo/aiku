<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 Feb 2023 19:13:13 Malaysia Time, Plane KL- Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('code')->nullable();
            $table->foreignId('shipper_id')->nullable()->constrained();
            $table->string('tracking')->nullable()->index();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('source_id')->nullable()->unique();

        });
    }


    public function down()
    {
        Schema::dropIfExists('shipments');
    }
};
