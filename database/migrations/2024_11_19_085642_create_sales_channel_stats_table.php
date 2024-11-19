<?php

use App\Stubs\Migrations\HasUsageStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasUsageStats;
    public function up(): void
    {
        Schema::create('sales_channel_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sales_channel_id')->index();
            $table->foreign('sales_channel_id')->references('id')->on('sales_channels');
            $table = $this->usageStats($table);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('sales_channel_stats');
    }
};
