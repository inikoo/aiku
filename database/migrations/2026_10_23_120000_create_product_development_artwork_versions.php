<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('product_development_artwork_versions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('artwork_id')->index();
            $table->foreign('artwork_id')->references('id')->on('product_development_artworks')->cascadeOnDelete();
            $table->unsignedSmallInteger('version');
            $table->string('source')->nullable();
            $table->unsignedInteger('media_id')->nullable()->index();
            $table->foreign('media_id')->references('id')->on('media')->nullOnDelete();
            $table->string('status')->default('in_review');
            $table->string('update_note')->nullable();
            $table->unsignedInteger('submitted_by_id')->nullable();
            $table->foreign('submitted_by_id')->references('id')->on('users');
            $table->timestampTz('submitted_at')->nullable();
            $table->text('review_comment')->nullable();
            $table->unsignedInteger('reviewed_by_id')->nullable();
            $table->foreign('reviewed_by_id')->references('id')->on('users');
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampsTz();
            $table->unique(['artwork_id', 'version']);
        });

        DB::statement("
            INSERT INTO product_development_artwork_versions (artwork_id, version, source, media_id, status, update_note, submitted_by_id, submitted_at, review_comment, reviewed_by_id, reviewed_at, created_at, updated_at)
            SELECT id, version, source, media_id, status, update_note, updated_by_id, artwork_updated_at, review_comment, reviewed_by_id, reviewed_at, now(), now()
            FROM product_development_artworks
            WHERE version > 0 AND status <> 'not_submitted'
            ON CONFLICT (artwork_id, version) DO NOTHING
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('product_development_artwork_versions');
    }
};
