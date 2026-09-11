<?php

namespace Database\Seeders;

use App\Models\Catalogue;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogueSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil semua kategori yang sudah di-seed
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('Harap jalankan CategorySeeder terlebih dahulu!');
            return;
        }

        // 2. Ambil user yang memiliki role 'seller' (menggunakan Spatie scope)
        $sellers = User::role('seller')->get();

        // Fallback: Jika tidak ada user dengan role seller, ambil user pertama yang ada di DB
        if ($sellers->isEmpty()) {
            $fallbackUser = User::first();
            if (!$fallbackUser) {
                $this->command->warn('Tidak ada User di database. Harap jalankan UserSeeder terlebih dahulu!');
                return;
            }
            $sellers = collect([$fallbackUser]);
        }

        // 3. Data contoh katalog produk
        $productTemplates = [
            [
                'title' => 'Mechanical Keyboard RGB',
                'description' => 'High-quality mechanical keyboard with brown switches and customizable RGB lighting.',
                'price' => 89.99,
                'stock' => 50,
                'weight' => 1000,
                'category_keyword' => 'Electronics'
            ],
            [
                'title' => 'Ergonomic Office Chair',
                'description' => 'Adjustable lumbar support ergonomic chair, perfect for long working hours.',
                'price' => 149.50,
                'stock' => 15,
                'weight' => 10000,
                'category_keyword' => 'Home & Living'
            ],
            [
                'title' => 'Oversized Cotton Hoodie',
                'description' => 'Unisex oversized hoodie made of 100% premium heavy cotton.',
                'price' => 35.00,
                'stock' => 100,
                'weight' => 1000,
                'category_keyword' => 'Fashion'
            ],
            [
                'title' => 'Atomic Habits - Paperback',
                'description' => 'An easy and proven way to build good habits and break bad ones by James Clear.',
                'price' => 16.00,
                'stock' => 40,
                'weight' => 1000,
                'category_keyword' => 'Books'
            ],
        ];

        // 4. Proses seeding
        foreach ($productTemplates as $template) {
            // Cari kategori yang cocok berdasarkan nama template atau default ke kategori pertama
            $category = $categories->first(function ($cat) use ($template) {
                return Str::contains($cat->name, $template['category_keyword']);
            }) ?? $categories->first();

            // Ambil seller secara acak untuk memvariasikan pemilik katalog
            $randomSeller = $sellers->random();

            Catalogue::query()->firstOrCreate(
                ['slug' => Str::slug($template['title'])],
                [
                    'id' => (string) Str::uuid(),
                    'user_id' => $randomSeller->id, // Terhubung ke seller asli
                    'category_id' => $category->id, // Terhubung ke kategori asli
                    'title' => $template['title'],
                    'weight' => $template['weight'],
                    'description' => $template['description'],
                    'price' => $template['price'],
                    'stock' => $template['stock'],
                    'status' => 'active',
                    'image' => null, // Atur default null, atau path dummy image jika dibutuhkan
                ]
            );
        }
    }
}