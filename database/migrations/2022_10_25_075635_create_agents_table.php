<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 11:28:24 Malaysia Time, Sanur, Bali, Indonesia
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
        Schema::create('agents', function (Blueprint $table) {
            $table->smallIncrements('id');
            $this->procurementSubject($table);
        });
        DB::statement('CREATE INDEX ON agents USING gin (name gin_trgm_ops) ');

    }


    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
