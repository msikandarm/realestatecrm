<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;

class CitySeeder extends Seeder
{
    public function run()
    {
        $cities = [
            ['name' => 'Karachi'],
            ['name' => 'Lahore'],
            ['name' => 'Islamabad'],
            ['name' => 'Rawalpindi'],
            ['name' => 'Faisalabad'],
        ];

        foreach ($cities as $c) {
            City::updateOrCreate(['name' => $c['name']], $c);
        }
    }
}
