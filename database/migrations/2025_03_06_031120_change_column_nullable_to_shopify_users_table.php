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
        Schema::table('shopify_users', function (Blueprint $table) {
            $table->unsignedSmallInteger('group_id')->nullable()->change();
            $table->unsignedSmallInteger('organisation_id')->nullable()->change();
            $table->unsignedInteger('customer_id')->nullable()->change();
            $table->string('username')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_users', function (Blueprint $table) {
            $table->unsignedSmallInteger('group_id')->index()->change();
            $table->unsignedSmallInteger('organisation_id')->change();
            $table->unsignedInteger('customer_id')->index()->change();
            $table->string('username')->nullable()->change();
        });
    }
};
