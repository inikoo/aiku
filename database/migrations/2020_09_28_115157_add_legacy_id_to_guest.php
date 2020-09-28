<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLegacyIdToGuest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->unsignedSmallInteger('legacy_id')->nullable();
            $table->unsignedSmallInteger('tenant_id');
            $table->json('data');
            $table->json('settings');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn('legacy_id');
            $table->dropColumn('tenant_id');
            $table->dropColumn('data');
            $table->dropColumn('settings');


        });
    }
}
