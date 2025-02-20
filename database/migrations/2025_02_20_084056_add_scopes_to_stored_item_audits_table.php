<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stored_item_audits', function (Blueprint $table) {
            $table->string('scope_type')->index()->comment('Fulfilment|Pallet')->nullable();
            $table->unsignedSmallInteger('scope_id')->nullable();
            $table->index(['scope_type', 'scope_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stored_item_audits', function (Blueprint $table) {
            $table->dropColumn('scope_type');
            $table->dropColumn('scope_id');
        });
    }
};
