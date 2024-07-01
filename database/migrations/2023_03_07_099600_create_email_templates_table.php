<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 12:27:04 Malaysia Time, Kuala Lumpur, Malaysia
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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table=$this->groupOrgRelationship($table);
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('type')->index()->nullable();
            $table->string('name');
            $table->morphs('parent');
            $table->json('data')->nullable();
            $table->json('compiled')->nullable();
            $table->unsignedInteger('screenshot_id')->nullable();
            $table->foreign('screenshot_id')->references('id')->on('media');
            $table->unsignedInteger('outbox_id');
            $table->foreign('outbox_id')->references('id')->on('outboxes');
            $table->unsignedInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->unsignedInteger('website_id')->nullable();
            $table->foreign('website_id')->references('id')->on('websites');
            $table->unsignedInteger('unpublished_snapshot_id')->nullable()->index();
            $table->boolean('is_seeded')->index()->default(false);
            $table->boolean('is_transactional')->index()->default(false);
            $table->timestampsTz();
            // Please fix all the rules here especially the nullable after we fix this to be more functional
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
