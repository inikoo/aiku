<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 29 Nov 2024 17:15:17 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasCommsStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasCommsStats;
    public function up(): void
    {
        Schema::create('outbox_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('outbox_id')->nullable();
            $table->foreign('outbox_id')->references('id')->on('outboxes');

            $table->unsignedInteger('number_subscribers')->default(0);
            $table->unsignedInteger('number_unsubscribed')->default(0);
            $table->unsignedSmallInteger('number_mailshots')->default(0);
            $table->unsignedSmallInteger('number_email_bulk_runs')->default(0);
            $table->unsignedSmallInteger('number_email_ongoing_runs')->default(0);

            $table = $this->dispatchedEmailStats($table);

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('outbox_stats');
    }
};