<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics'],
            ['name' => 'Fashion & Apparel'],
            ['name' => 'Home & Living'],
            ['name' => 'Books & Stationeries'],
        ];

        foreach ($categories as $category) {
            Category::query()->firstOrCreate(
                // Unik berdasarkan slug untuk menghindari duplikasi saat di-seed ulang
                ['slug' => Str::slug($category['name'])],
                [
                    'id' => (string) Str::uuid(), // Menghasilkan UUID manual jika model tidak meng-handle-nya otomatis di seeder
                    'name' => $category['name'],
                ]
            );
        }
    }
}