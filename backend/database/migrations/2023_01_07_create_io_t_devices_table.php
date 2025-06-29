<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('io_t_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('property_id')->nullable(); // Optional: device might not be tied to specific property
            $table->string('device_id')->unique(); // Unique identifier for the actual IoT device (e.g., MAC address, serial)
            $table->string('name');
            $table->string('type'); // e.g., 'light', 'lock', 'thermostat', 'sensor'
            $table->string('status')->nullable(); // Current operational status (e.g., 'on', 'off', 'locked', 'unlocked')
            $table->json('last_reading')->nullable(); // Last telemetry reading (e.g., {"temperature": 25.5, "humidity": 60})
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('set null'); // If property is deleted, keep device but nullify property_id
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('io_t_devices');
    }
};
