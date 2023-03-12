<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Models\Traits\Stubs\HasDispatchedEmailStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasDispatchedEmailStats;
    public function up()
    {
        Schema::create('mailshot_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('mailshot_id')->nullable();
            $table->foreign('mailshot_id')->references('id')->on('mailshots');
            $table=$this->dispatchedEmailStats($table);

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('mailshot_stats');
    }
};
