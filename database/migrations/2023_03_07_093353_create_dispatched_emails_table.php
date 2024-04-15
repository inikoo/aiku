<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('dispatched_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('outbox_id')->nullable();
            $table->foreign('outbox_id')->references('id')->on('outboxes');
            $table->unsignedSmallInteger('mailshot_id')->nullable();
            $table->foreign('mailshot_id')->references('id')->on('mailshots');
            $table->unsignedInteger('email_address_id')->nullable();
            $table->foreign('email_address_id')->references('id')->on('email_addresses');
            $table->string('ses_id')->nullable()->index();
            $table->string('recipient_type')->nullable();
            $table->unsignedInteger('recipient_id')->nullable();
            $table->index(['recipient_type','recipient_id']);
            $table->string('state')->default(DispatchedEmailStateEnum::READY->value);
            $table->timestampsTz();
            $table->dateTimeTz('sent_at')->nullable();
            $table->dateTimeTz('first_read_at')->nullable();
            $table->dateTimeTz('last_read_at')->nullable();
            $table->dateTimeTz('first_clicked_at')->nullable();
            $table->dateTimeTz('last_clicked_at')->nullable();
            $table->unsignedSmallInteger('number_reads')->default(0);
            $table->unsignedSmallInteger('number_clicks')->default(0);
            $table->boolean('mask_as_spam')->default(false);
            $table->boolean('provoked_unsubscribe')->default(false);
            $table->jsonb('data');
            $table->string('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('dispatched_emails');
    }
};
