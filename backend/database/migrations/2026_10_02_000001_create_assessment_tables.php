<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2B — assessment aggregate, revisions, answers, immutable snapshots,
 * domain results, idempotency receipts and the transactional outbox
 * (03-DATABASE-SCHEMA.md "Assessment and result tables").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('cycle_id')->constrained('assessment_cycles')->restrictOnDelete();
            $table->foreignUlid('framework_version_id')->constrained()->restrictOnDelete();
            $table->ulid('current_submission_id')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'cycle_id'], 'uq_assessment_org_cycle');
            $table->unique(['id', 'framework_version_id'], 'uq_assessment_version');
        });

        Schema::create('assessment_revisions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('assessment_id');
            $table->ulid('framework_version_id');
            $table->unsignedInteger('revision_number');
            $table->ulid('parent_revision_id')->nullable();
            $table->string('status', 12)->default('draft'); // draft | submitted
            $table->string('correction_reason', 1000)->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->foreignUlid('created_by')->constrained('app_users')->restrictOnDelete();
            $table->foreignUlid('submitted_by')->nullable()->constrained('app_users')->restrictOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            // Assessment id while a draft is open, NULL once submitted: one open draft per assessment.
            $table->char('open_draft_for', 26)->charset('ascii')->collation('ascii_bin')->nullable()
                ->storedAs("CASE WHEN status = 'draft' THEN assessment_id ELSE NULL END");
            $table->unique('open_draft_for', 'uq_one_open_draft');
            $table->unique(['assessment_id', 'revision_number'], 'uq_revision_number');
            $table->unique(['id', 'assessment_id'], 'uq_revision_assessment');
            $table->unique(['id', 'framework_version_id'], 'uq_revision_version');
            $table->foreign(['assessment_id', 'framework_version_id'], 'fk_revision_assessment_version')
                ->references(['id', 'framework_version_id'])->on('assessments')->restrictOnDelete();
            $table->foreign(['parent_revision_id', 'assessment_id'], 'fk_revision_parent_same_assessment')
                ->references(['id', 'assessment_id'])->on('assessment_revisions')->restrictOnDelete();
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->foreign(['current_submission_id', 'id'], 'fk_assessment_current_submission')
                ->references(['id', 'assessment_id'])->on('assessment_revisions')->restrictOnDelete();
        });

        Schema::create('assessment_answers', function (Blueprint $table) {
            $table->ulid('revision_id');
            $table->ulid('framework_version_id');
            $table->ulid('question_id');
            $table->boolean('answer')->nullable(); // 1 = yes, 0 = no, NULL = unanswered
            $table->timestamp('updated_at')->nullable();
            $table->primary(['revision_id', 'question_id']);
            $table->foreign(['revision_id', 'framework_version_id'], 'fk_answer_revision_version')
                ->references(['id', 'framework_version_id'])->on('assessment_revisions')->restrictOnDelete();
            $table->foreign(['question_id', 'framework_version_id'], 'fk_answer_question_version')
                ->references(['id', 'framework_version_id'])->on('framework_questions')->restrictOnDelete();
        });

        Schema::create('submission_snapshots', function (Blueprint $table) {
            $table->ulid('revision_id')->primary();
            $table->unsignedTinyInteger('overall_yes_count');
            $table->decimal('overall_percent', 5, 2);
            $table->string('band', 16);
            $table->string('schema_version', 20);
            $table->string('scoring_version', 20);
            $table->json('snapshot');
            $table->char('canonical_sha256', 64);
            $table->timestamp('submitted_at');
            $table->foreign('revision_id', 'fk_snapshot_revision')->references('id')->on('assessment_revisions')->restrictOnDelete();
        });

        Schema::create('submission_domain_results', function (Blueprint $table) {
            $table->ulid('revision_id');
            $table->ulid('framework_version_id');
            $table->ulid('domain_id');
            $table->unsignedTinyInteger('yes_count');
            $table->unsignedTinyInteger('score_percent');
            $table->string('band', 16);
            $table->primary(['revision_id', 'domain_id']);
            $table->index(['framework_version_id', 'domain_id', 'band'], 'ix_domain_result_portfolio');
            $table->foreign('revision_id', 'fk_domain_result_snapshot')->references('revision_id')->on('submission_snapshots')->restrictOnDelete();
            $table->foreign(['domain_id', 'framework_version_id'], 'fk_domain_result_domain_version')
                ->references(['id', 'framework_version_id'])->on('framework_domains')->restrictOnDelete();
        });

        Schema::create('idempotency_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('actor_id')->constrained('app_users')->restrictOnDelete();
            $table->string('operation', 60);
            $table->string('idempotency_key', 120);
            $table->char('request_hash', 64);
            $table->string('status', 12)->default('pending'); // pending | completed
            $table->string('response_reference', 26)->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->unique(['actor_id', 'operation', 'idempotency_key'], 'uq_idempotency_actor_key');
        });

        Schema::create('outbox_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('event_type', 80);
            $table->string('aggregate_type', 40);
            $table->ulid('aggregate_id');
            $table->json('payload');
            $table->string('status', 12)->default('pending'); // pending | delivered | failed
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('available_at')->useCurrent();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'available_at'], 'ix_outbox_pending');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_events');
        Schema::dropIfExists('idempotency_requests');
        Schema::dropIfExists('submission_domain_results');
        Schema::dropIfExists('submission_snapshots');
        Schema::dropIfExists('assessment_answers');
        Schema::table('assessments', fn (Blueprint $t) => $t->dropForeign('fk_assessment_current_submission'));
        Schema::dropIfExists('assessment_revisions');
        Schema::dropIfExists('assessments');
    }
};
