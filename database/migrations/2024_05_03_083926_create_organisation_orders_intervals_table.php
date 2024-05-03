<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 17:36:19 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasDateIntervalsStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasDateIntervalsStats;

    public function up(): void
    {
        Schema::create('organisation_orders_intervals', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');

            $table=$this->dateIntervals($table, ['in_baskets', 'in_process', 'in_process_paid', 'in_warehouse', 'packed', 'in_dispatch_area', 'delivery_notes']);

            $table->timestampsTz();
            $table->unique(['organisation_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('organisation_orders_intervals');
    }
};
