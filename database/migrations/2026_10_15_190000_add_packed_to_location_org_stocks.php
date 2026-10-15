<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('location_org_stocks', function (Blueprint $table) {
            $table->timestampTz('packed_at')->nullable();
            $table->unsignedSmallInteger('packed_by_user_id')->nullable();
            $table->foreign('packed_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->decimal('packed_quantity', 16, 6)->nullable()->comment('quantity at the location when it was ticked packed, a different quantity now means the tick is stale');
        });
    }

    public function down(): void
    {
        Schema::table('location_org_stocks', function (Blueprint $table) {
            $table->dropForeign(['packed_by_user_id']);
            $table->dropColumn(['packed_at', 'packed_by_user_id', 'packed_quantity']);
        });
    }
};
