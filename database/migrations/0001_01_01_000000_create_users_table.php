<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 255)->unique('users_email_unique');
            $table->string('phone', 50)->nullable();
            $table->string('password', 255);
            $table->enum('type', ['super_admin', 'admin', 'sales_agent'])
                ->default('sales_agent')
                ->index('users_type_index');
            $table->boolean('is_active')->default(true)->index('users_is_active_index');

            // TOTP 2FA
            $table->boolean('2fa_enabled')->default(false);
            $table->text('2fa_secret')->nullable();
            $table->timestamp('2fa_confirmed_at')->nullable();

            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->index('user_sessions_user_id_index')
                ->constrained('users', 'id', 'user_sessions_user_id_foreign')
                ->cascadeOnDelete();
            $table->foreignId('sanctum_token_id')
                ->nullable()
                ->unique('user_sessions_sanctum_token_unique')
                ->constrained('personal_access_tokens', 'id', 'user_sessions_sanctum_token_id_foreign')
                ->cascadeOnDelete();
            $table->char('session_identifier', 36)->unique('user_sessions_session_identifier_unique');
            $table->string('authentication_type', 30)->index('user_sessions_authentication_type_index');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_name', 255)->nullable();
            $table->string('device_type', 50)->nullable();
            $table->string('os_name', 100)->nullable();
            $table->string('os_version', 100)->nullable();
            $table->string('browser_name', 100)->nullable();
            $table->string('browser_version', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->dateTime('login_at');
            $table->dateTime('last_activity_at')->nullable()->index('user_sessions_last_activity_at_index');
            $table->dateTime('logout_at')->nullable();
            $table->string('last_activity_ip', 45)->nullable();
            $table->boolean('is_active')->default(true)->index('user_sessions_is_active_index');
            $table->dateTime('revoked_at')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();
    }
};
