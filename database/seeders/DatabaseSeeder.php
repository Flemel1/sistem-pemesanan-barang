<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use MatanYadaev\EloquentSpatial\Objects\Point;

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

        \App\Models\Setting::create([
            'order_deliver_fee' => 4500,
            'location' => new Point(-7.7871936589657, 110.35144329071)
        ]);
        \App\Models\Category::create([
            'category_name' => 'beras',
        ]);
        \App\Models\Category::create([
            'category_name' => 'telur',
        ]);
        \App\Models\Category::create([
            'category_name' => 'minyak',
        ]);
        \App\Models\Category::create([
            'category_name' => 'minuman',
        ]);
        \App\Models\Category::create([
            'category_name' => 'snack',
        ]);
    }
}
