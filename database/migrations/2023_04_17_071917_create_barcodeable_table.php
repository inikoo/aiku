<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 15:40:18 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('barcodeable', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('barcode_id')->index();
            $table->morphs('barcodeable');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('barcodeable');
    }
};
