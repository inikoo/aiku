<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('dispatched_emails', function (Blueprint $table) {
            $table->unsignedInteger('number_email_tracking_events')->default(0);
        });
    }


    public function down(): void
    {
        Schema::table('dispatched_emails', function (Blueprint $table) {
            $table->dropColumn('number_email_tracking_events');
        });
    }
};
