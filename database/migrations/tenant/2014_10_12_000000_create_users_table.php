<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 19 Sept 2022 23:17:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->boolean('status')->default(true);
            $table->nullableMorphs('parent');
            // Used for tables
            $table->string('name')->nullable()->comment('No normal, mirror parent name');
            $table->rememberToken();
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->timestampsTz();
            $table->string('password');
            $table->uuid('global_id')->index();

        });
    }


    public function down()
    {
        Schema::dropIfExists('users');
    }
};
