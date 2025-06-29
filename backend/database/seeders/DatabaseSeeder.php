<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Stancl\Tenancy\Features\TenantConfig; // For setting tenant config
use App\Models\User; // Import User model

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call tenant-agnostic seeders first if any, e.g.:
        // $this->call(SomeCentralDataSeeder::class);

        // Create example tenants
        $tenantA = \App\Models\Tenant::create([
            'name' => 'Tenant A Hotel',
            'subdomain' => 'tenanta',
            'branding_settings' => ['theme_color' => '#3B82F6', 'logo_url' => 'https://placehold.co/100x50/3B82F6/FFFFFF?text=TenantA'],
        ]);
        $tenantB = \App\Models\Tenant::create([
            'name' => 'Tenant B Resort',
            'subdomain' => 'tenantb',
            'branding_settings' => ['theme_color' => '#8B5CF6', 'logo_url' => 'https://placehold.co/100x50/8B5CF6/FFFFFF?text=TenantB'],
        ]);
        
        // Seed each tenant's database
        // Important: Use tenant()->central(function (...) { ... }); for central database operations
        // and tenant()->run(function (...) { ... }); for tenant database operations.

        // Seed Tenant A's database
        \Stancl\Tenancy\Facades\Tenancy::byTenant($tenantA)->run(function() use ($tenantA) {
            $this->call(RolesAndPermissionsSeeder::class); // Seed roles and permissions for this tenant
            User::factory()->create([
                'tenant_id' => $tenantA->id,
                'name' => 'Tenant A Admin',
                'email' => 'admin_a@test.com',
            ])->assignRole('tenant_admin'); // Assign tenant_admin role

            // Create some properties for Tenant A
            \App\Models\Property::factory()->count(3)->create([
                'tenant_id' => $tenantA->id,
            ]);

            // Add some sample cultural content for Tenant A
            \App\Models\CultureContent::create([
                'tenant_id' => $tenantA->id,
                'title' => '台北美食之旅',
                'content' => '探索台北夜市的小吃文化，品嚐臭豆腐和牛肉麵。',
                'language' => 'zh_TW',
                'category' => '美食',
            ]);
            \App\Models\CultureContent::create([
                'tenant_id' => $tenantA->id,
                'title' => 'Taipei Food Tour',
                'content' => 'Explore the night market food culture in Taipei, tasting stinky tofu and beef noodles.',
                'language' => 'en',
                'category' => 'Food',
            ]);
             \App\Models\CultureContent::create([
                'tenant_id' => $tenantA->id,
                'title' => '台北のグルメツアー',
                'content' => '台北のナイトマーケットで臭豆腐と牛肉麺を味わい、食文化を探検しましょう。',
                'language' => 'ja',
                'category' => '料理',
            ]);

            // Add some IoT devices for Tenant A
            \App\Models\IoTDevice::factory()->count(2)->create([
                'tenant_id' => $tenantA->id,
                'property_id' => \App\Models\Property::where('tenant_id', $tenantA->id)->inRandomOrder()->first()->id,
            ]);
        });

        // Seed Tenant B's database
        \Stancl\Tenancy\Facades\Tenancy::byTenant($tenantB)->run(function() use ($tenantB) {
            $this->call(RolesAndPermissionsSeeder::class); // Seed roles and permissions for this tenant
            User::factory()->create([
                'tenant_id' => $tenantB->id,
                'name' => 'Tenant B Admin',
                'email' => 'admin_b@test.com',
            ])->assignRole('tenant_admin'); // Assign tenant_admin role

            // Create some properties for Tenant B
            \App\Models\Property::factory()->count(2)->create([
                'tenant_id' => $tenantB->id,
            ]);

             // Add some sample cultural content for Tenant B
            \App\Models\CultureContent::create([
                'tenant_id' => $tenantB->id,
                'title' => '墾丁海灘活動',
                'content' => '在墾丁享受水上活動和陽光，探索熱帶風情。',
                'language' => 'zh_TW',
                'category' => '戶外',
            ]);
            \App\Models\CultureContent::create([
                'tenant_id' => $tenantB->id,
                'title' => 'Kenting Beach Activities',
                'content' => 'Enjoy water sports and sunshine in Kenting, exploring the tropical ambiance.',
                'language' => 'en',
                'category' => 'Outdoor',
            ]);
        });
    }
}
