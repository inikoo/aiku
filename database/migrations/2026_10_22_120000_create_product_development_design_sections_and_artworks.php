<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * HELP-4211: packaging and label rounds become design sections with one artwork per SKU, and the
 * packaging and label stages merge into one artwork stage. Existing developments are test data, so
 * their packaging and label rounds are dropped (files stay) rather than converted.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('product_development_design_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_development_id')->index();
            $table->foreign('product_development_id')->references('id')->on('product_developments')->cascadeOnDelete();
            $table->string('name', 64);
            $table->string('description')->nullable();
            $table->string('packaging_supplier')->nullable();
            $table->string('editor_kind')->default('sheet');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestampsTz();
        });

        Schema::create('product_development_artworks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_development_id')->index();
            $table->foreign('product_development_id')->references('id')->on('product_developments')->cascadeOnDelete();
            $table->unsignedInteger('design_section_id')->index();
            $table->foreign('design_section_id')->references('id')->on('product_development_design_sections')->cascadeOnDelete();
            $table->unsignedInteger('product_development_item_id')->index();
            $table->foreign('product_development_item_id')->references('id')->on('product_development_items')->cascadeOnDelete();
            $table->string('source')->nullable();
            $table->unsignedInteger('media_id')->nullable()->index();
            $table->foreign('media_id')->references('id')->on('media')->nullOnDelete();
            $table->jsonb('layout')->default('{}');
            $table->unsignedSmallInteger('version')->default(0);
            $table->string('status')->default('not_submitted')->index();
            $table->string('update_note')->nullable();
            $table->unsignedInteger('updated_by_id')->nullable();
            $table->foreign('updated_by_id')->references('id')->on('users');
            $table->timestampTz('artwork_updated_at')->nullable();
            $table->text('review_comment')->nullable();
            $table->unsignedInteger('reviewed_by_id')->nullable();
            $table->foreign('reviewed_by_id')->references('id')->on('users');
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampTz('locked_at')->nullable();
            $table->timestampsTz();
            $table->unique(['design_section_id', 'product_development_item_id']);
        });

        $this->moveExistingDevelopments();
    }

    /**
     * Safe to run again: only developments that still have packaging or label stages are renumbered and
     * get the Box and Label sections.
     */
    private function moveExistingDevelopments(): void
    {
        $now = now();

        foreach (DB::table('product_developments')->get(['id', 'current_position', 'data']) as $development) {
            $stages = DB::table('product_development_stages')
                ->where('product_development_id', $development->id)
                ->whereIn('stage', ['packaging', 'label'])
                ->get();

            if ($stages->isNotEmpty()) {
                $states = $stages->pluck('state')->all();
                $done   = array_diff($states, ['completed', 'skipped']) === [];

                DB::table('product_development_stages')->insert([
                    'product_development_id' => $development->id,
                    'position'               => 4,
                    'sub_position'           => null,
                    'stage'                  => 'artwork',
                    'role'                   => 'designer',
                    'state'                  => match (true) {
                        $done && array_diff($states, ['skipped']) === [] => 'skipped',
                        $done => 'completed',
                        array_intersect($states, ['in_progress', 'completed', 'skipped']) !== [] => 'in_progress',
                        default => 'pending',
                    },
                    'started_at'      => $stages->pluck('started_at')->filter()->min(),
                    'due_at'          => $stages->pluck('due_at')->filter()->max(),
                    'completed_at'    => $done ? $stages->pluck('completed_at')->filter()->max() : null,
                    'completed_by_id' => $done ? $stages->sortByDesc('completed_at')->first()->completed_by_id : null,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);

                DB::table('product_development_stages')->whereIn('id', $stages->pluck('id'))->delete();
                DB::table('product_development_stages')->where('product_development_id', $development->id)->where('position', '>=', 6)->decrement('position');

                $position = (int) $development->current_position;
                if ($position >= 4) {
                    DB::table('product_developments')->where('id', $development->id)->update([
                        'current_position' => $position <= 5 ? 4 : $position - 1,
                    ]);
                }

                if (!DB::table('product_development_design_sections')->where('product_development_id', $development->id)->exists()) {
                    DB::table('product_development_design_sections')->insert([
                        ['product_development_id' => $development->id, 'name' => 'Box', 'editor_kind' => 'box', 'position' => 1, 'created_at' => $now, 'updated_at' => $now],
                        ['product_development_id' => $development->id, 'name' => 'Label', 'editor_kind' => 'sheet', 'position' => 2, 'created_at' => $now, 'updated_at' => $now],
                    ]);
                }
            }

            $data = json_decode($development->data ?? '{}', true) ?: [];
            if (isset($data['skipped_rounds'])) {
                unset($data['skipped_rounds']['packaging'], $data['skipped_rounds']['label']);
                DB::table('product_developments')->where('id', $development->id)->update(['data' => json_encode($data)]);
            }
        }

        $oldRoundIds = DB::table('product_development_design_rounds')->whereIn('round', ['packaging', 'label'])->pluck('id');
        if ($oldRoundIds->isNotEmpty()) {
            DB::table('product_development_comments')
                ->where('model_type', 'ProductDevelopmentDesignRound')
                ->whereIn('model_id', $oldRoundIds)
                ->update(['model_type' => null, 'model_id' => null]);
            DB::table('product_development_design_rounds')->whereIn('id', $oldRoundIds)->delete();
        }

        DB::table('staff_tasks')
            ->where('model_type', 'ProductDevelopment')
            ->whereIn('status', ['todo', 'in_progress'])
            ->where(fn ($query) => $query
                ->where('data->product_development_event', 'design_packaging')
                ->orWhere('data->product_development_event', 'design_label'))
            ->update(['status' => 'cancelled', 'updated_at' => $now]);
    }

    public function down(): void
    {
        Schema::dropIfExists('product_development_artworks');
        Schema::dropIfExists('product_development_design_sections');
    }
};
