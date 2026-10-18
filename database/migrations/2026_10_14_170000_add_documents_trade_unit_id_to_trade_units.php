<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('trade_units', function (Blueprint $table) {
            $table->unsignedInteger('documents_trade_unit_id')->nullable()->index()->comment('IFRA, SDS, CLP and allergen documents are taken from this trade unit (another size of the same substance) instead of its own');
            $table->foreign('documents_trade_unit_id')->references('id')->on('trade_units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trade_units', function (Blueprint $table) {
            $table->dropForeign(['documents_trade_unit_id']);
            $table->dropColumn('documents_trade_unit_id');
        });
    }
};
