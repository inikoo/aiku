<?php

use App\Stubs\Migrations\HasDropshippingStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasDropshippingStats;

    public function up(): void
    {
        Schema::create('customer_dropshipping_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onUpdate('cascade')->onDelete('cascade');

            $this->stats($table);

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('customer_dropshipping_stats');
    }
};
