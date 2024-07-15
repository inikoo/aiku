<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 Jul 2024 13:35:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('shipping_stats', function (Blueprint $table) {
            $table->id();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('shipping_stats');
    }
};
