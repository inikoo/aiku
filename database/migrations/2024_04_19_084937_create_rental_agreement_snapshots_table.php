<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 29 Apr 2024 09:13:36 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('rental_agreement_snapshots', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('rental_agreement_id')->index();
            $table->foreign('rental_agreement_id')->references('id')->on('rental_agreements');
            $table->jsonb('data');
            $table->dateTimeTz('date');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_agreement_snapshots');
    }
};
