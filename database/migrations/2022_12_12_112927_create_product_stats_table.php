<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Dec 2022 19:37:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Market\Outer\OuterStateEnum;
use App\Stubs\Migrations\HasSalesStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesStats;

    public function up(): void
    {
        Schema::create('product_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id')->index();
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedSmallInteger('number_outers')->default(0);
            $table->unsignedSmallInteger('number_outers_available')->default(0);
            foreach (OuterStateEnum::cases() as $case) {
                $table->unsignedInteger('number_outers_state_'.$case->snake())->default(0);
            }

            $table->unsignedSmallInteger('number_historic_outerables')->default(0);

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('product_stats');
    }
};
