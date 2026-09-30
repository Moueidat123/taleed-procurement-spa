<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Application identity foundation (docs/production/03-DATABASE-SCHEMA.md, decisions.md D-03/D-05).
 *
 * `app_users` belongs to the independent Eloquent `web` guard. The single
 * Statamic CMS administrator is file-based and has no row here. Password
 * reset tokens are kept in separate tables per provider so neither identity
 * domain can resolve the other's tokens.
 *
 * Organization, invitation and verification-challenge tables arrive in Phase 2.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            // Nullable until the organizations table exists (Phase 2 adds the FK).
            $table->ulid('organization_id')->nullable()->index();
            $table->string('name', 200);
            $table->string('email', 254)->unique();
            $table->string('job_title', 200)->nullable();
            $table->string('password');
            $table->enum('role', ['champion', 'analyst', 'admin']);
            $table->boolean('can_export')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            // Laravel Fortify TOTP (privileged MFA, decisions.md D-17).
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('app_password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 254)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('cms_password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 254)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('cms_password_activation_tokens', function (Blueprint $table) {
            $table->string('email', 254)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            // String identifier: app users use ULIDs (not Laravel's default bigint).
            $table->string('user_id', 64)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('cms_password_activation_tokens');
        Schema::dropIfExists('cms_password_reset_tokens');
        Schema::dropIfExists('app_password_reset_tokens');
        Schema::dropIfExists('app_users');
    }
};
