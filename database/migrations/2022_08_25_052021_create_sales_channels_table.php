<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('sales_channels', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('code')->unique();
            $table->string('name');
            $table->dateTimeTz('fetched_at')->nullable();
            $table->string('source_id')->nullable();
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('sales_channels');
    }
};
