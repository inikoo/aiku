<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use App\Stubs\Migrations\HasSoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    use HasSoftDeletes;
    public function up(): void
    {
        Schema::create('mailshots', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);
            $table->unsignedSmallInteger('shop_id')->nullable()->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('subject')->index();

            $table->unsignedSmallInteger('outbox_id')->nullable()->index();
            $table->foreign('outbox_id')->references('id')->on('outboxes');
            $table->unsignedSmallInteger('email_template_id')->nullable()->index();
            $table->foreign('email_template_id')->references('id')->on('email_templates');
            $table->string('state')->index()->default(MailshotStateEnum::IN_PROCESS->value);

            $table->dateTimeTz('date')->index();
            $table->dateTimeTz('ready_at')->nullable();
            $table->dateTimeTz('start_sending_at')->nullable();
            $table->dateTimeTz('sent_at')->nullable();
            $table->dateTimeTz('cancelled_at')->nullable();
            $table->dateTimeTz('stopped_at')->nullable();
            $table->jsonb('layout');
            $table->jsonb('recipients_recipe');
            $table->unsignedSmallInteger('publisher_id')->nullable()->comment('org user');
            $table->foreign('publisher_id')->references('id')->on('users');
            $table->string('parent_type')->index();
            $table->unsignedInteger('parent_id')->index();


            $table->jsonb('data');
            $table->timestampsTz();
            $table = $this->softDeletes($table);
            $table->string('source_id')->nullable()->unique();
            $table->index(['parent_type', 'parent_id']);

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('mailshots');
    }
};
