<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The business catalog. Prices live here and nowhere else: the AI Business Assistant may
     * only quote a price it has just read through a tool call.
     */
    public function up(): void
    {
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // product | service | package | consultation | digital | addon
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('translations')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('image_url')->nullable();
            $table->json('tags')->nullable(); // popular, new, promo, local, eco, bestseller, signature, ...
            $table->json('requirements')->nullable(); // appointment, deposit, documents, site_visit, internet, ...
            $table->string('delivery')->nullable(); // onsite | pickup | delivery | online | shipping
            $table->unsignedSmallInteger('duration_minutes')->nullable(); // length of one session, when it has one
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_unavailable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['business_id', 'slug']);
            $table->index(['business_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};
