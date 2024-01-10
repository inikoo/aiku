<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 12:27:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('address', 254)->unique();
            $table->boolean('is_hard_bounced')->default(false);
            $table->string('hard_bounce_type')->nullable();
            //https://stackoverflow.com/questions/1199190/what-is-the-optimal-length-for-an-email-address-in-a-database
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
