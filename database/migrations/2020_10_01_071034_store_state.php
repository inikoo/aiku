<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StoreState extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('state')->index();
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
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('state');
            $table->dropSoftDeletesTz('deleted_at');
        }
        );
    }
}
