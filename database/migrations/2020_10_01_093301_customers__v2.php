<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomersV2 extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table(
            'customers', function (Blueprint $table) {
            $table->string('name')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('mobile')->nullable()->index();
            $table->string('status')->index();
            $table->string('state')->index();
            $table->string('country_id')->nullable()->index();

            $table->unsignedMediumInteger('billing_address_id')->nullable()->index();
            $table->foreign('billing_address_id')->references('id')->on('addresses');
            $table->unsignedMediumInteger('delivery_address_id')->nullable()->index();
            $table->foreign('delivery_address_id')->references('id')->on('addresses');


            $table->softDeletesTz('deleted_at', 0);
        }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table(
            'customers', function (Blueprint $table) {
            $table->dropColumn('state');
            $table->dropColumn('email');
            $table->dropColumn('mobile');
            $table->dropColumn('status');
            $table->dropColumn('billing_address_id');
            $table->dropColumn('delivery_address_id');
            $table->dropColumn('country_id');

            $table->dropSoftDeletesTz('deleted_at');
        }
        );
    }
}
