<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2B — versioned framework content and assessment cycles
 * (03-DATABASE-SCHEMA.md "Versioned framework tables"). Published versions are
 * immutable (enforced in FrameworkVersion and by the publish command).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('framework_versions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('semantic_version', 20)->unique();
            $table->string('status', 12)->default('draft'); // draft | published
            $table->string('title', 200);
            $table->string('source_file_name', 200);
            $table->char('source_sha256', 64);
            $table->char('content_sha256', 64);
            $table->string('scoring_version', 20);
            $table->json('interpretations');
            $table->string('approval_reference', 200)->nullable();
            $table->ulid('approved_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('framework_domains', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('framework_version_id')->constrained()->restrictOnDelete();
            $table->string('key', 60);
            $table->string('title', 200);
            $table->unsignedTinyInteger('position');
            $table->unique(['framework_version_id', 'key'], 'uq_domain_key');
            $table->unique(['framework_version_id', 'position'], 'uq_domain_position');
            $table->unique(['id', 'framework_version_id'], 'uq_domain_version');
        });

        Schema::create('framework_questions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('framework_version_id');
            $table->ulid('domain_id');
            $table->string('source_question_id', 10); // string: "1.10" is not "1.1"
            $table->unsignedTinyInteger('position');
            $table->text('text');
            $table->string('source_cell', 80);
            $table->unique(['framework_version_id', 'source_question_id'], 'uq_question_source_id');
            $table->unique(['domain_id', 'position'], 'uq_question_position');
            $table->unique(['id', 'framework_version_id'], 'uq_question_version');
            $table->foreign(['domain_id', 'framework_version_id'], 'fk_question_domain_version')
                ->references(['id', 'framework_version_id'])->on('framework_domains')->restrictOnDelete();
        });

        Schema::create('recommendation_actions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('framework_version_id');
            $table->ulid('domain_id');
            $table->string('band', 16);
            $table->unsignedTinyInteger('position');
            $table->string('source_action_id', 80);
            $table->text('text');
            $table->string('source_cell', 80);
            $table->unique(['domain_id', 'band', 'position'], 'uq_action_position');
            $table->foreign(['domain_id', 'framework_version_id'], 'fk_action_domain_version')
                ->references(['id', 'framework_version_id'])->on('framework_domains')->restrictOnDelete();
        });

        Schema::create('assessment_cycles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('framework_version_id')->constrained()->restrictOnDelete();
            $table->string('title', 200);
            $table->string('status', 12)->default('open'); // open | closed
            $table->timestamp('opens_at');
            $table->timestamp('closes_at');
            $table->string('business_timezone', 40)->default('Asia/Riyadh');
            // Generated: 1 while open, NULL otherwise, so at most one open cycle.
            $table->unsignedTinyInteger('open_flag')->nullable()
                ->storedAs("CASE WHEN status = 'open' THEN 1 ELSE NULL END");
            $table->unique('open_flag', 'uq_one_open_cycle');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_cycles');
        Schema::dropIfExists('recommendation_actions');
        Schema::dropIfExists('framework_questions');
        Schema::dropIfExists('framework_domains');
        Schema::dropIfExists('framework_versions');
    }
};
