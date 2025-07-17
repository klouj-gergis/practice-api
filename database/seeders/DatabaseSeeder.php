<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Call your Roles and Permissions Seeder first
        $this->call(RolesAndPermissionsSeeder::class);

        // Create 1 admin
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'type' => 'admin',
        ]);
        $admin->assignRole('admin');

        // Create 5 customers
        $customers = User::factory(5)->create();
        foreach ($customers as $customer) {
            $customer->assignRole('customer');
        }

        // Create 2 delivery users
        $deliveries = User::factory(2)->create([
            'type' => 'delivery',
        ]);
        foreach ($deliveries as $delivery) {
            $delivery->assignRole('delivery');
        }

        // Create 20 products
        Product::factory(20)->create();
    }
}
