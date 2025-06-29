<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->index();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('language', 5)->index(); // e.g., 'en', 'zh_TW', 'ja'
            $table->string('title');
            $table->text('description');
            $table->json('keywords')->nullable(); // JSON array of strings
            $table->timestamps();
            $table->unique(['property_id', 'language', 'tenant_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('seo_contents');
    }
};
