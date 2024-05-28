<?php

namespace Database\Seeders;

use App\Models\TransactionTypes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name_EN' => 'pay Cash',
                'name_AR' => 'دفع نقداً'
            ],
            [
                'name_EN' => 'recieve Cash',
                'name_AR' => 'تلقي نقداً'
            ]
        ];

        foreach ($types as $type)
        {
            TransactionTypes::create($type);
        }
    }
}
