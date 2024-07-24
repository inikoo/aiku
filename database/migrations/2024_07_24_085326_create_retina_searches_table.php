<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jul 2024 16:53:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    public function up(): void
    {
        Schema::create('retina_searches', function (Blueprint $table) {
            $table->increments('id');
            $table=$this->groupOrgRelationship($table);
            $table->unsignedSmallInteger('website_id')->nullable()->index();
            $table->foreign('website_id')->references('id')->on('websites');

            $table->unsignedSmallInteger('customer_id')->nullable()->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->jsonb('web_users');



            $table->nullableMorphs('model');

            $table->longText('haystack_tier_1')->nullable();
            $table->longText('haystack_tier_2')->nullable();
            $table->longText('haystack_tier_3')->nullable();

            $table->jsonb('sections');

            $table->jsonb('permissions');
            $table->jsonb('result');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('retina_searches');
    }
};
