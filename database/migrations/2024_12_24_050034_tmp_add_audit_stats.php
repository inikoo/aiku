<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 24 Dec 2024 13:00:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasSysAdminStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasSysAdminStats;
    public function up(): void
    {
        // Schema::table('organisation_stats', function (Blueprint $table) {
        //     $this->auditFields($table);
        // });
        // Schema::table('group_sysadmin_stats', function (Blueprint $table) {
        //     $this->auditFields($table);
        // });
        // Schema::table('user_stats', function (Blueprint $table) {
        //     $this->auditFieldsForNonSystem($table);
        // });
        // Schema::table('web_user_stats', function (Blueprint $table) {
        //     $this->auditFieldsForNonSystem($table);
        // });
    }


    public function down(): void
    {
        //
    }
};
