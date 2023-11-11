<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 20:10:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('email_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->string('notification_id')->nullable()->index();
            $table->unsignedInteger('dispatched_email_id')->index();
            $table->foreign('dispatched_email_id')->references('id')->on('dispatched_emails');
            $table->string('type');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('email_tracking_events');
    }
};
