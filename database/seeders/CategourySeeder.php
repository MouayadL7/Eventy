<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoury;
class CategourySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Categoury::create(['name'=>'Places']);
        Categoury::create(['name'=>'Catering']);
        Categoury::create(['name'=>'Sound']);
        Categoury::create(['name'=>'Decoration']);
        Categoury::create(['name'=>'Photography']);
        Categoury::create(['name'=>'Desert']);
        Categoury::create(['name'=>'Car']);
        Categoury::create(['name'=>'Organizers']);
        Categoury::create(['name'=>'Cards']);
        Categoury::create(['name'=>'Makeupartist']);
        

    }
}
