<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('external_links', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->integer('number_websites_shown')->default(0);
            $table->integer('number_webpages_shown')->default(0);
            $table->integer('number_web_blocks_shown')->default(0);
            $table->integer('number_websites_hidden')->default(0);
            $table->integer('number_webpages_hidden')->default(0);
            $table->integer('number_web_blocks_hidden')->default(0);
            $table->string('status');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('external_links');
    }
};
