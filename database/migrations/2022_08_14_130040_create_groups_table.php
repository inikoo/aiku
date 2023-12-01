<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Nov 2023 23:03:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('owner_id')->nullable()->comment('Organisation who owns this model');
            $table->ulid()->index();
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('subdomain')->nullable()->unique()->collation('und_ns');
            $table->string('code');
            $table->string('name');
            $table->unsignedSmallInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->smallInteger('number_organisations')->default(0);
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
