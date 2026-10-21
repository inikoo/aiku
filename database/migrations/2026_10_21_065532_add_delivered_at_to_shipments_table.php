<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dateTimeTz('delivered_at')->nullable()->index();
            $table->string('carrier_status')->nullable()->comment('Last status the carrier reported, as the carrier words it');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['delivered_at', 'carrier_status']);
        });
    }
};
