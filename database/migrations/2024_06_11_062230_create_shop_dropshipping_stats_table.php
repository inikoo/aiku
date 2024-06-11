<?php

use App\Stubs\Migrations\HasDropshippingStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasDropshippingStats;

    public function up(): void
    {

        Schema::create('shop_dropshipping_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('shop_id');
            $table->foreign('shop_id')->references('id')->on('shops')->onUpdate('cascade')->onDelete('cascade');

            $this->stats($table);

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('shop_dropshipping_stats');
    }
};
