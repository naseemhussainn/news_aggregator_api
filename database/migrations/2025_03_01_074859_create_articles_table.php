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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('url');
            $table->string('image_url')->nullable();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('external_id')->nullable(); // ID from the API
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            // Index for faster searching
            $table->index('title');
            $table->index('published_at');
            
            // Foreign keys
            $table->foreign('source_id')->references('id')->on('sources')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
