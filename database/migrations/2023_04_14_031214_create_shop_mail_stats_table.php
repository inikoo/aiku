<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Apr 2023 15:22:38 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Stubs\Migrations\HasMailStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasMailStats;

    public function up()
    {
        Schema::create('shop_mail_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('shop_id');
            $table->foreign('shop_id')->references('id')->on('shops')->onUpdate('cascade')->onDelete('cascade');
            $table=$this->outboxesStats($table);
            $table=$this->mailshotsStats($table);
            $table=$this->dispatchedEmailStats($table);

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('shop_mail_stats');
    }
};
