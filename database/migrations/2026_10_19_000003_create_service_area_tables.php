<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('translations')->nullable();
            $table->string('area_type')->default('storefront'); // storefront | workshop | online | onsite
            $table->unsignedTinyInteger('min_attendees')->default(1);
            $table->unsignedTinyInteger('max_attendees')->default(4);
            $table->json('features')->nullable();
            $table->decimal('booking_fee', 12, 2)->default(0); // deposit per booking, 0 = free
            $table->decimal('minimum_order', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['business_id', 'slug']);
        });

        Schema::create('service_area_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_area_id')->constrained()->cascadeOnDelete();
            $table->string('image_path')->nullable();
            $table->string('image_url')->nullable();
            $table->json('tags')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        /**
         * Mock booking data: the one table the AI is NEVER allowed to
         * answer from memory. A POS / booking-system adapter can replace
         * it later without touching the AI or knowledge-base layers.
         */
        Schema::create('slot_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_area_id')->constrained()->cascadeOnDelete();
            $table->date('booking_date');
            $table->string('time_slot', 5); // HH:MM, the start of the slot
            $table->unsignedSmallInteger('total_units');
            $table->unsignedSmallInteger('reserved_units')->default(0);
            $table->timestamps();
            $table->unique(['service_area_id', 'booking_date', 'time_slot'], 'slot_inventory_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_inventory');
        Schema::dropIfExists('service_area_images');
        Schema::dropIfExists('service_areas');
    }
};
