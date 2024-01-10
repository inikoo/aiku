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
        Schema::table('shops', function (Blueprint $table) {
            $table->unsignedSmallInteger('sender_email_id')->nullable();
            $table->foreign('sender_email_id')->references('id')->on('sender_emails');
            $table->unsignedSmallInteger('prospects_sender_email_id')->nullable();
            $table->foreign('prospects_sender_email_id')->references('id')->on('sender_emails');
        });
    }


    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('sender_email_id');
            $table->dropColumn('prospects_sender_email_id');
        });
    }
};
