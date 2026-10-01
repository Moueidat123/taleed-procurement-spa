<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2A — organizations, the app-user organization relationship, staff
 * invitations, email verification challenges and the append-only audit log
 * (docs/production/03-DATABASE-SCHEMA.md; PLAN-CONTRACT §2 "Phase 2A").
 *
 * Decisions honoured here:
 *  - D-31 public Champion sign-up; one Champion per company; duplicate company
 *    name or registration id is blocked (normalized columns support detection).
 *  - No sector field (PLAN-CONTRACT §3, D-contract) — the approved profile only.
 *  - D-30 six-digit email verification: hashed challenge, 15 min, 5 attempts.
 *  - D-10 staff invitations: 72-hour, single-use, hashed token.
 * Tokens and codes are stored only as hashes; no recoverable plaintext. [S7]
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('display_name', 200);
            // Lower-cased, collapsed-whitespace copy for duplicate review.
            $table->string('normalized_name', 200);
            $table->string('country_code', 2);
            $table->string('size_band', 40);
            $table->string('registration_id', 120)->nullable();
            $table->string('normalized_registration_id', 120)->nullable();
            $table->timestamp('authority_confirmed_at')->nullable();
            $table->ulid('authority_confirmed_by')->nullable();
            $table->boolean('active')->default(true);
            // Flagged test organizations are excluded from portfolio figures (default).
            $table->boolean('is_test')->default(false);
            $table->timestamps();

            $table->index(['normalized_name', 'country_code'], 'idx_org_name_country');
            // Duplicate registration id within a country is blocked when provided.
            $table->unique(['country_code', 'normalized_registration_id'], 'uq_org_registration');
        });

        // Now that organizations exists, attach the app-user foreign key.
        Schema::table('app_users', function (Blueprint $table) {
            $table->foreign('organization_id')
                ->references('id')->on('organizations')
                ->nullOnDelete();
        });

        Schema::create('staff_invitations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('email', 254);
            $table->string('normalized_email', 254);
            $table->enum('role', ['analyst', 'admin']);
            $table->boolean('can_export')->default(false);
            // SHA-256 of the single-use token; the plaintext is only ever emailed.
            $table->string('token_hash', 64)->unique();
            $table->ulid('inviter_id');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->ulid('accepted_user_id')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index('normalized_email', 'idx_invite_email');
            $table->foreign('inviter_id')->references('id')->on('app_users')->cascadeOnDelete();
        });

        Schema::create('email_verification_challenges', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('app_user_id');
            // SHA-256 of the six-digit code; never the code itself.
            $table->string('challenge_hash', 64);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            // Throttles resend: last send time and count within the window.
            $table->timestamp('last_sent_at')->nullable();
            $table->unsignedSmallInteger('sent_count')->default(1);
            $table->timestamps();

            $table->index(['app_user_id', 'consumed_at'], 'idx_challenge_user');
            $table->foreign('app_user_id')->references('id')->on('app_users')->cascadeOnDelete();
        });

        Schema::create('audit_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            // Nullable actor: some events (e.g. anonymous registration attempts) have none.
            $table->ulid('actor_id')->nullable();
            $table->string('action', 80);
            $table->string('target_type', 80)->nullable();
            $table->string('target_id', 64)->nullable();
            $table->ulid('organization_id')->nullable();
            $table->string('outcome', 20)->default('ok');
            // Minimal metadata only: never passwords, tokens or full answer payloads.
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['action', 'created_at'], 'idx_audit_action');
            $table->index(['organization_id', 'created_at'], 'idx_audit_org');
            $table->index(['actor_id', 'created_at'], 'idx_audit_actor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('email_verification_challenges');
        Schema::dropIfExists('staff_invitations');
        Schema::table('app_users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
        Schema::dropIfExists('organizations');
    }
};
