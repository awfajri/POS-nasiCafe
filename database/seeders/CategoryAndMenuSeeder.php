<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryAndMenuSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        $categories = [
            ['name' => 'Nasi Spesial', 'description' => 'Aneka nasi andalan NasiCafe.'],
            ['name' => 'Lauk Pauk', 'description' => 'Lauk pendamping nasi.'],
            ['name' => 'Minuman', 'description' => 'Minuman dingin dan hangat.'],
            ['name' => 'Camilan', 'description' => 'Camilan gurih dan manis.'],
        ];

        $categoryIds = [];

        foreach ($categories as $data) {
            $category = Category::query()->updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                ],
            );

            $categoryIds[$data['name']] = $category->id;
        }

        // [nama, kategori, harga, stok, min_stock]
        $menus = [
            // Nasi Spesial
            ['Nasi Goreng Spesial', 'Nasi Spesial', 25000, 20, 5],
            ['Nasi Ayam Bakar Madu', 'Nasi Spesial', 28000, 15, 5],
            ['Nasi Ayam Geprek Sambal Bawang', 'Nasi Spesial', 22000, 3, 5],   // low stock
            ['Nasi Gila NasiCafe', 'Nasi Spesial', 30000, 10, 5],
            // Lauk Pauk
            ['Ayam Goreng Lengkuas', 'Lauk Pauk', 18000, 12, 5],
            ['Bebek Goreng Crispy', 'Lauk Pauk', 25000, 2, 5],                 // low stock
            ['Tempe Mendoan', 'Lauk Pauk', 8000, 0, 5],                        // out of stock
            ['Tahu Crispy Cabe Garam', 'Lauk Pauk', 10000, 8, 5],
            // Minuman
            ['Kopi Susu Gula Aren', 'Minuman', 18000, 25, 5],
            ['Teh Manis Dingin', 'Minuman', 6000, 1, 5],                       // low stock
            ['Jus Alpukat', 'Minuman', 15000, 10, 5],
            ['Lemon Tea Segar', 'Minuman', 12000, 14, 5],
            // Camilan
            ['Pisang Goreng Crispy', 'Camilan', 12000, 18, 5],
            ['Kentang Goreng Keju', 'Camilan', 15000, 22, 5],
        ];

        foreach ($menus as [$name, $categoryName, $price, $stock, $minStock]) {
            Menu::query()->updateOrCreate(
                ['name' => $name],
                [
                    'category_id' => $categoryIds[$categoryName],
                    'price' => $price,
                    'stock' => $stock,
                    'min_stock' => $minStock,
                    'image_path' => null,
                    'is_available' => true,
                ],
            );
        }
    }
}
