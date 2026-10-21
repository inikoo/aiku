<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('pallet_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('pallet_returns', 'customer_courier')) {
                $table->string('customer_courier')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pallet_returns', function (Blueprint $table) {
            if (Schema::hasColumn('pallet_returns', 'customer_courier')) {
                $table->dropColumn('customer_courier');
            }
        });
    }
};
