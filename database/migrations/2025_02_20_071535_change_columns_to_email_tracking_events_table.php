<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_tracking_events', function (Blueprint $table) {
            $table->string('ip')->nullable();
            $table->string('device')->nullable();
            $table->dropColumn(['provider_reference']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_tracking_events', function (Blueprint $table) {
            $table->dropColumn(['ip']);
            $table->dropColumn(['device']);
            $table->string('provider_reference')->nullable();
        });
    }
};
