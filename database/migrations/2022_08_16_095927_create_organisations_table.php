<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Mar 2023 23:04:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Stubs\Migrations\HasAssets;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasAssets;
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups');
            $table->ulid()->index();
            $table->string('type')->index()->default(OrganisationTypeEnum::SHOP->value);
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('code')->index();
            $table->string('name');
            $table->string('email')->nullable();
            $table->boolean('status')->default(true);
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->jsonb('source');
            $table = $this->assets($table);
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};
