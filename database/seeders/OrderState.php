<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OrderState;


use Illuminate\Support\Facades\DB;

class OrderStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   OrderState::create(['name'=>'pending']);
        OrderState::create(['name'=>'in preparation']);
        OrderState::create(['name'=>'done']);
        // DB::table('orders')->updateOrInsert(['state' => 'pending']);
        // DB::table('orders')->updateOrInsert(['state' => 'in preparation']);
        // DB::table('orders')->updateOrInsert(['state' => 'done']);
    }
}
