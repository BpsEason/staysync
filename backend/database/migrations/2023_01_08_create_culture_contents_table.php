<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('culture_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('title');
            $table->text('content');
            $table->string('language', 5)->index(); // e.g., 'en', 'zh_TW', 'ja'
            $table->string('category')->nullable(); // e.g., 'local_cuisine', 'historical_site', 'festival'
            $table->string('image_url')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'title', 'language']); // Ensure unique content per tenant per language
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('culture_contents');
    }
};
