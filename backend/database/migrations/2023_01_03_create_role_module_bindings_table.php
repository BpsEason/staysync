<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_module_bindings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('module'); // e.g., 'bookings', 'properties', 'iot', 'seo', 'culture'
            $table->json('permissions')->comment('e.g., {"read": true, "write": false, "control": true}');
            $table->timestamps();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            // Ensure unique combination of role, module, and tenant
            $table->unique(['role_id', 'module', 'tenant_id'], 'role_module_tenant_unique');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('role_module_bindings');
    }
};
