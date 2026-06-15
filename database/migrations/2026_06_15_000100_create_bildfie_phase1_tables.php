<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * bildfie — Phase 1: Core Marketplace (developer spec §4).
 *
 * Architecture rules (spec §3) reflected here:
 *  - Escrow is tied to a job and released only on owner approval.
 *  - Photo proof is required to mark work complete (enforced in app logic).
 *  - Reviews are permanent; communication stays on-platform (audit trail).
 *
 * Later phases (trust layer, milestones, CRM) add their own migrations.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Professional profile (spec §4.1)
        Schema::create('professional_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('username')->unique(); // public URL: /pro/{username}
            $table->string('photo_url')->nullable();
            $table->string('bio', 200)->nullable();
            $table->string('trade');               // single trade tag
            $table->unsignedSmallInteger('years_exp')->nullable();
            $table->string('area')->nullable();
            $table->enum('availability', ['available_now', 'booked'])->default('available_now');
            $table->date('booked_until')->nullable();
            $table->boolean('is_live')->default(false); // hidden until photo + 3 portfolio + availability
            $table->timestamps();
        });

        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_profile_id')->constrained()->cascadeOnDelete();
            $table->string('photo_url');
            $table->string('caption');
            $table->string('location');
            $table->unsignedInteger('approx_cost_kes');
            $table->timestamps();
        });

        // References visible only to an owner who hired the contractor (enforced in app).
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->timestamps();
        });

        // Job posting (spec §4.2)
        Schema::create('jobs_board', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users');
            $table->string('title');
            $table->string('trade');
            $table->text('description');
            $table->string('city');
            $table->string('area')->nullable();
            $table->string('map_pin')->nullable();
            $table->unsignedInteger('budget_kes'); // fixed amount, not a range
            $table->date('start_date')->nullable();
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->enum('status', [
                'open', 'bid_accepted', 'in_progress',
                'completed_pending_approval', 'released', 'disputed', 'cancelled',
            ])->default('open');
            $table->timestamps();
        });

        Schema::create('job_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs_board')->cascadeOnDelete();
            $table->string('photo_url');
            $table->timestamps();
        });

        // Bidding (spec §4.3)
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs_board')->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained('users');
            $table->unsignedInteger('price_kes'); // fixed quote for the whole job
            $table->unsignedSmallInteger('timeline_days');
            $table->string('pitch', 300);
            $table->enum('status', ['submitted', 'accepted', 'rejected'])->default('submitted');
            $table->timestamps();
            $table->unique(['job_id', 'professional_id']);
        });

        // Escrow (spec §4.4 / §4.5) — full amount held, 2.5% fee at release.
        Schema::create('escrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->unique()->constrained('jobs_board')->cascadeOnDelete();
            $table->unsignedInteger('amount_kes');
            $table->unsignedSmallInteger('fee_bps')->default(250); // 2.5%
            $table->enum('status', ['awaiting_funding', 'funded', 'released', 'frozen', 'refunded'])
                ->default('awaiting_funding');
            $table->timestamp('funded_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('auto_release_at')->nullable(); // 48h after marked complete
            $table->timestamps();
        });

        // Reviews (spec §4.6) — permanent, public, no edit/delete.
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs_board');
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('subject_id')->constrained('users');
            $table->unsignedTinyInteger('rating'); // 1..5
            $table->string('body', 400);
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->unique(['job_id', 'author_id']);
        });

        // Disputes (spec §4.7) — manual review at launch.
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs_board');
            $table->foreignId('raised_by_id')->constrained('users');
            $table->text('description');
            $table->string('photo_url'); // at least 1 photo evidence required
            $table->enum('status', ['open', 'resolved'])->default('open');
            $table->timestamps();
        });

        // Notifications (spec §4.8) — in-app + SMS.
        Schema::create('notifications_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('channel', ['in_app', 'sms']);
            $table->string('type');
            $table->string('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_log');
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('escrows');
        Schema::dropIfExists('bids');
        Schema::dropIfExists('job_photos');
        Schema::dropIfExists('jobs_board');
        Schema::dropIfExists('references');
        Schema::dropIfExists('portfolio_items');
        Schema::dropIfExists('professional_profiles');
    }
};
