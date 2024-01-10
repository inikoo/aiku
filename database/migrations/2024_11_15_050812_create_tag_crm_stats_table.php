<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Nov 2023 13:08:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Stubs\Migrations\HasProspectStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasProspectStats;

    public function up(): void
    {
        Schema::create('tag_crm_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tag_id')->unique();
            $table->foreign('tag_id')->references('id')->on('tags');

            $table->unsignedInteger('number_customers')->default(0);

            foreach (CustomerStateEnum::cases() as $customerState) {
                $table->unsignedInteger("number_customers_state_{$customerState->snake()}")->default(0);
            }

            $this->prospectsStats($table);



        });

        Schema::table('tag_crm_stats', function (Blueprint $table) {
            $this->prospectsPrepareForStatsVersion2($table);
        });

        Schema::table('tag_crm_stats', function (Blueprint $table) {
            $this->prospectsStatsVersion2($table);
            $table->timestampsTz();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('tag_crm_stats');
    }
};
