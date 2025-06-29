<?php

use Stancl\Tenancy\Features\ForceHttps;
use Stancl\Tenancy\Features\TenantConfig;
use Stancl\Tenancy\Features\UserImpersonation;
use Stancl\Tenancy\Database\TenantScope;
use App\Models\Tenant; // Your custom Tenant model

return [
    'tenant_model' => Tenant::class,
    'id_generator' => Stancl\Tenancy\UidGenerators\Sha256UidGenerator::class,
    'central_domains' => [ // Central domains that do NOT use tenancy
        'localhost', // For local development, or your main app domain
        '127.0.0.1',
    ],
    'db' => [
        'prefix' => 'tenant',
        'suffix' => '',
    ],
    'storage_to_path' => [
        'app/' => base_path('tenant-data/app/'),
        'framework/cache/' => base_path('tenant-data/framework/cache/'),
        'framework/sessions/' => base_path('tenant-data/framework/sessions/'),
        'framework/views/' => base_path('tenant-data/framework/views/'),
        'logs/' => base_path('tenant-data/logs/'),
        'temp/' => base_path('tenant-data/temp/'),
    ],
    'features' => [
        // Features that are enabled for tenants.
        // Add more as needed.
        TenantConfig::class,
        // ForceHttps::class, // Enable if you want to force HTTPS for tenant domains
        // UserImpersonation::class, // For tenant admin impersonation of tenant users
    ],
    'models_to_affect' => [
        // Models that should be tenant-aware.
        // Make sure these models use the TenantScope trait or explicitly apply it.
        \App\Models\User::class,
        \App\Models\Property::class,
        \App\Models\Booking::class,
        \App\Models\SeoContent::class,
        \App\Models\IoTDevice::class,
        \App\Models\CultureContent::class,
        \App\Models\Role::class, // Spatie roles can be tenant-aware
        \App\Models\Permission::class, // Spatie permissions can be tenant-aware
        \App\Models\RoleModuleBinding::class,
    ],
    'route_middlewares' => [
        'universal' => [ // Middlewares that run on all domains (central and tenant)
            \Stancl\Tenancy\Http\Middleware\PreventAccessFromCentralDomains::class,
            \Stancl\Tenancy\Http\Middleware\InitializeTenancyByDomain::class,
        ],
        'api' => [ // Middlewares specific to tenant API routes (after tenancy is initialized)
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ],
    ],
    // Event handlers for tenancy lifecycle.
    // Example: When a tenant is created, you might want to create a default admin user.
    'events' => [
        'tenant_created' => [
            // \App\Listeners\InitializeTenantDatabase::class,
            // \App\Listeners\CreateDefaultTenantUser::class,
        ],
        'tenant_initialized' => [
            //
        ],
        'tenant_deleted' => [
            //
        ],
    ],
];
