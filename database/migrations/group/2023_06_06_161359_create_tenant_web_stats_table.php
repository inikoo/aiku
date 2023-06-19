<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 07 Jun 2023 02:30:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Web\Website\WebsiteStateEnum;
use App\Stubs\Migrations\HasWebStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasWebStats;
    public function up(): void
    {
        Schema::create('tenant_web_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('public.tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('number_websites')->default(0);
            $table->unsignedInteger('number_websites_under_maintenance')->default(0);
            foreach (WebsiteStateEnum::cases() as $websiteState) {
                $table->unsignedInteger('number_websites_state_'.$websiteState->snake())->default(0);
            }
            $table=$this->webStats($table);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('tenant_web_stats');
    }
};
