<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Accessory;

class AccessorySeeder extends Seeder {
    public function run() {
        Accessory::insert([
            ['name' => 'Default Mattress', 'price' => 0, 'is_default' => true],
            ['name' => 'Default Pillow', 'price' => 0, 'is_default' => true],
            ['name' => 'Extra Blanket', 'price' => 500, 'is_default' => false],
            ['name' => 'Study Lamp', 'price' => 300, 'is_default' => false]
        ]);
    }
}

