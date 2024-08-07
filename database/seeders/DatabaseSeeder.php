<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(RoleSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(BudgetSeeder::class);
        $this->call(TransactionTypesSeeder::class);
        $this->call(TransactionStatusesSeeder::class);
        $this->call(OrderStateSeeder::class);
        $this->call(CategourySeeder::class);
    }
}
