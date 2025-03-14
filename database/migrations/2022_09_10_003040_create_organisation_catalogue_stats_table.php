<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Nov 2023 23:22:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasCatalogueStats;
use App\Stubs\Migrations\HasOrderingStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasCatalogueStats;
    use HasOrderingStats;

    public function up(): void
    {
        Schema::create('organisation_catalogue_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations')
                ->onUpdate('cascade')->onDelete('cascade');
            $table = $this->shopsStatsFields($table);
            $table = $this->catalogueStats($table);
            $table = $this->billableFields($table);

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('organisation_catalogue_stats');
    }
};
