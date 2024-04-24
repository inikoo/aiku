<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Apr 2024 11:18:51 Malaysia Time, Plane Abu Dhabi - Manchester
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('outers', function (Blueprint $table) {
            $table->foreign('current_historic_outerable_id')->references('id')->on('historic_outerables');
        });
    }


    public function down(): void
    {
        Schema::table('outers', function (Blueprint $table) {
            $table->dropForeign('current_historic_outerable_id_foreign');
        });
    }
};
