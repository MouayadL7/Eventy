<?php

namespace Database\Seeders;

use App\Models\TransactionStatuses;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name_EN' => 'complete',
                'name_AR' => 'مكتمل'
            ],
            [
                'name_EN' => 'cancel',
                'name_AR' => 'ملغي'
            ]
        ];

        foreach ($statuses as $status)
        {
            TransactionStatuses::create($status);
        }
    }
}
