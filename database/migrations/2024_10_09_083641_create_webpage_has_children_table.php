<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 09 Oct 2024 19:37:23 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('webpage_has_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('webpage_id')->index();
            $table->foreign('webpage_id')->references('id')->on('webpages');
            $table->unsignedInteger('child_id')->index();
            $table->foreign('child_id')->references('id')->on('webpages');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('webpage_has_children');
    }
};
