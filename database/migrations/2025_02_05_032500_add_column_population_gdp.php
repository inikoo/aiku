<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->unsignedInteger('population')->nullable()->index();
            $table->unsignedInteger('gdp')->nullable()->index();
        });
    }


    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('population');
            $table->dropColumn('gdp');
        });
    }
};
