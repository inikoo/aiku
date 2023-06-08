<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 08 Jun 2023 14:34:48 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasAudit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasAudit;

    public function up(): void
    {
        $tableName = config('audit.drivers.database.table', 'audits');

        Schema::create($tableName, function (Blueprint $table) {
            $this->getAuditFields($table);
        });
    }


    public function down(): void
    {
        $tableName = config('audit.drivers.database.table', 'audits');

        Schema::dropIfExists($tableName);
    }
};
