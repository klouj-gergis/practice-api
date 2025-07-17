<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define Products permissions
        Permission::firstOrCreate(['name' => 'view products']);
        Permission::firstOrCreate(['name' => 'create products']);
        Permission::firstOrCreate(['name' => 'edit products']);
        Permission::firstOrCreate(['name' => 'delete products']);

        // Define Order Permissions
        Permission::firstOrCreate(['name' => 'view orders']);
        Permission::firstOrCreate(['name' => 'create orders']);
        Permission::firstOrCreate(['name' => 'update orders']);
        Permission::firstOrCreate(['name' => 'cancel orders']);

        // Define User Permission
        Permission::firstOrCreate(['name' => 'view users']);
        Permission::firstOrCreate(['name' => 'edit users']);

        // Define Delivery Permissions
        Permission::firstOrCreate(['name' => 'view deliveries']);
        Permission::firstOrCreate(['name' => 'update delivery status']);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view orders',
            'update orders',
            'cancel orders',
            'view users',
            'edit users',
            'view deliveries',
        ]);

        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        $customerRole->givePermissionTo([
            'view products',
            'view orders',
            'create orders',
            'update orders',
            'cancel orders',
        ]);

        $deliveryRole = Role::firstOrCreate(['name' => 'delivery']);
        $deliveryRole->givePermissionTo([
            'view deliveries',
            'update delivery status',
            'view orders',
            'view products',
        ]);
    }
}
