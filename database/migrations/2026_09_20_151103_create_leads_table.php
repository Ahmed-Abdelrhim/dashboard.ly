<?php

use App\Enums\LeadStatus;
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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_number', 30)->unique();

            // Basic information
            $table->string('name', 255);
            $table->string('email', 255)->nullable()->index();
            $table->string('phone', 50)->nullable()->index();
            $table->unsignedTinyInteger('age')->nullable();

            // WAVEX relationship
            $table->unsignedBigInteger('wavex_user_id')->nullable()->index();
            $table->unsignedBigInteger('country_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();

            // Lead source / campaign
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();

            // Form information
            $table->boolean('tried_aqua_fitness')->nullable();
            $table->json('interested_in')->nullable();
            $table->string('sessions_considering', 50)->nullable();
            $table->json('preferred_days')->nullable();
            $table->json('preferred_times')->nullable();

            // Marketing attribution
            $table->string('utm_source', 255)->nullable();
            $table->string('utm_medium', 255)->nullable();
            $table->string('utm_campaign', 255)->nullable();
            $table->string('utm_term', 255)->nullable();
            $table->string('utm_content', 255)->nullable();

            // Ad click identifiers
            $table->string('fbclid', 255)->nullable();
            $table->string('gclid', 255)->nullable();
            $table->string('ttclid', 255)->nullable();

            // Attribution context
            $table->text('landing_page_url')->nullable();
            $table->text('referrer_url')->nullable();

            // Sales assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // Status
            $table->enum('status', LeadStatus::values())
                ->default(LeadStatus::New->value)
                ->index();

            $table->timestamp('first_contacted_at')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->string('lost_reason', 255)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
