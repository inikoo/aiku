<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('snapshot_webpage_stats', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('webpage_id');
            $table->foreign('webpage_id')->references('id')->on('webpages')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('number_web_blocks')->default(0);
            $table->unsignedInteger('number_menu_columns')->default(0);
            $table->unsignedInteger('number_menu_items')->default(0);
            $table->unsignedInteger('number_columns')->default(0);
            $table->unsignedInteger('number_header_columns')->default(0);
            $table->unsignedInteger('number_footer_columns')->default(0);
            $table->unsignedInteger('height_desktop')->nullable();
            $table->unsignedInteger('height_mobile')->nullable();
            $table->unsignedInteger('number_internal_links')->default(0);
            $table->unsignedInteger('number_external_links')->default(0);
            $table->unsignedInteger('number_images')->default(0);
            $table->unsignedInteger('filesize')->default(0);

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('snapshot_webpage_stats');
    }
};
