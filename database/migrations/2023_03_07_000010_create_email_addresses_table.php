<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 Mar 2023 23:13:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('email_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->dateTimeTz('soft_bounced_at')->nullable()->index();
            $table->dateTimeTz('hard_bounced_at')->nullable()->index();
            $table->unsignedSmallInteger('number_dispatches')->default(0);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('email_addresses');
    }
};
