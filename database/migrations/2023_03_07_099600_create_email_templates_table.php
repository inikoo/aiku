<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 12:27:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Mail\EmailTemplate\EmailTemplateStateEnum;
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
            $table->string('type')->index()->nullable();
            $table->json('data')->nullable();
            $table->unsignedInteger('screenshot_id')->nullable();
            $table->foreign('screenshot_id')->references('id')->on('media');
            $table->unsignedInteger('outbox_id');
            $table->foreign('outbox_id')->references('id')->on('outboxes');
            $table->string('state')->index()->default(EmailTemplateStateEnum::IN_PROCESS);
            $table->unsignedInteger('unpublished_snapshot_id')->nullable()->index();
            $table->unsignedInteger('live_snapshot_id')->nullable()->index();
            $table->jsonb('published_layout');
            $table->dateTimeTz('live_at')->nullable();

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
