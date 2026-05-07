<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Manager User
        User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
        ]);

        // Create Regular User
        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Categories
        $electronics = Category::create(['name' => 'Electronics', 'description' => 'Electronic gadgets and devices']);
        $furniture = Category::create(['name' => 'Furniture', 'description' => 'Home and office furniture']);
        $clothing = Category::create(['name' => 'Clothing', 'description' => 'Apparel and fashion']);

        // Suppliers
        $techCorp = Supplier::create(['name' => 'TechCorp', 'phone' => '123-456-7890', 'address' => '123 Tech St, Silicon Valley']);
        $homeDepot = Supplier::create(['name' => 'HomeDepot', 'phone' => '098-765-4321', 'address' => '456 Furniture Rd, New York']);

        // Products
        Product::create([
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'price' => 1200.00,
            'quantity' => 10,
            'unit' => 'pcs',
            'min_stock_level' => 5,
            'category_id' => $electronics->id,
            'supplier_id' => $techCorp->id,
        ]);

        Product::create([
            'name' => 'Smartphone',
            'sku' => 'PHN-002',
            'price' => 800.00,
            'quantity' => 2,
            'unit' => 'pcs',
            'min_stock_level' => 5,
            'category_id' => $electronics->id,
            'supplier_id' => $techCorp->id,
        ]);

        Product::create([
            'name' => 'Office Chair',
            'sku' => 'CHR-003',
            'price' => 150.00,
            'quantity' => 50,
            'unit' => 'pcs',
            'min_stock_level' => 10,
            'category_id' => $furniture->id,
            'supplier_id' => $homeDepot->id,
        ]);
    }
}
