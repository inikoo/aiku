<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:48:03 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Stubs\Migrations\HasDateIntervalsStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasDateIntervalsStats;
    public function up(): void
    {
        Schema::create('sales_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->string('model_type');
            $table->unsignedInteger('model_id');
            $table->string('scope');
            $table=$this->dateIntervals($table, ['shop_amount','org_amount','group_amount']);
            $table->index(['model_id', 'model_type']);
            $table->unique(['model_id', 'model_type', 'scope']);

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('sales_stats');
    }
};
