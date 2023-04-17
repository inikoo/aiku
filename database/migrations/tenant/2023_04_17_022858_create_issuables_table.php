<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:32:44 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('issuables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('issue_id')->index();
            $table->foreign('issue_id')->references('id')->on('issues');
            $table->morphs('issuable');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('issuables');
    }
};
