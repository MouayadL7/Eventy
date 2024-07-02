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
    {
        OrderState::create([
            'name_EN' => 'Pending',
            'name_AR' => 'قيد الانتظار'
        ]);

        OrderState::create([
            'name_EN' => 'In Preparation',
            'name_AR' => 'قيد التحضير'
        ]);

        OrderState::create([
            'name_EN' => 'Done',
            'name_AR' => 'تم القبول'
        ]);
    }
}
