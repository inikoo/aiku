<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 13:17:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasProcurementSubject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasProcurementSubject;

    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['supplier', 'sub-supplier'])->index()->comment('sub-supplier: agents supplier');
            $table->unsignedSmallInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('agents');
            $this->procurementSubject($table);

        });
        DB::statement('CREATE INDEX ON suppliers USING gin (name gin_trgm_ops) ');

    }


    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
