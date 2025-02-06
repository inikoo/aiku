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
        Schema::table('stored_item_movements', function (Blueprint $table) {
            $table->unsignedInteger('stored_item_audit_id')->nullable()->index();
            $table->foreign('stored_item_audit_id')->references('id')->on('stored_item_audits')->onDelete('cascade');
            $table->unsignedInteger('stored_item_audit_delta_id')->nullable()->index();
            $table->foreign('stored_item_audit_delta_id')->references('id')->on('stored_item_audit_deltas')->onDelete('cascade');
            $table->unsignedInteger('pallet_delivery_id')->nullable()->index();
            $table->foreign('pallet_delivery_id')->references('id')->on('pallet_deliveries')->onDelete('cascade');
            $table->unsignedInteger('pallet_id')->nullable()->index();
            $table->foreign('pallet_id')->references('id')->on('pallets')->onDelete('cascade');
            $table->unsignedInteger('pallet_return_id')->nullable()->index();
            $table->foreign('pallet_return_id')->references('id')->on('pallet_returns')->onDelete('cascade');
            $table->unsignedInteger('pallet_return_item_id')->nullable()->index();
            $table->foreign('pallet_return_item_id')->references('id')->on('pallet_return_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stored_item_movements', function (Blueprint $table) {
            $table->dropForeign(['stored_item_audit_id']);
            $table->dropForeign(['stored_item_audit_delta_id']);
            $table->dropForeign(['pallet_delivery_id']);
            $table->dropForeign(['pallet_id']);
            $table->dropForeign(['pallet_return_id']);
            $table->dropForeign(['pallet_return_item_id']);
        });
    }
};
