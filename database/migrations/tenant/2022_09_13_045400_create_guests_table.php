<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Sept 2022 12:54:23 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Auth\Guest\GuestTypeEnum;
use App\Stubs\Migrations\HasContact;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasContact;

    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique()->collation('und_ns');
            $table->boolean('status')->index()->default(true);
            $table->string('type')->default(GuestTypeEnum::CONTRACTOR->value);
            $table = $this->contactFields(table: $table, withPersonalDetails: true);
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
        });
        DB::statement('CREATE INDEX ON guests USING gin (remove_accents(contact_name) gin_trgm_ops) ');


    }


    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
