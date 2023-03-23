<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Mar 2023 16:50:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasDispatchedEmailStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasDispatchedEmailStats;

    public function up()
    {
        Schema::create('mailroom_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('mailroom_id')->nullable();
            $table->foreign('mailroom_id')->references('id')->on('mailrooms');

            $table->unsignedSmallInteger('number_outboxes')->default(0);
            $table->unsignedSmallInteger('number_mailshots')->default(0);
            $table=$this->dispatchedEmailStats($table);

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('mailroom_stats');
    }
};
