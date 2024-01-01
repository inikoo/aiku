<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 05 Apr 2023 13:54:16 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('media_stock', function (Blueprint $table) {
            $table->unsignedInteger('stock_id')->index();
            $table->string('type')->index();
            $table->foreign('stock_id')->references('id')->on('stocks');
            $table->unsignedInteger('media_id')->index();
            $table->unique(['stock_id', 'media_id']);
            $table->string('owner_type')->index();
            $table->unsignedInteger('owner_id');
            $table->boolean('public')->default(false)->index();

            $table->timestampsTz();
            $table->index(['owner_type', 'owner_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('media_stock');
    }
};
