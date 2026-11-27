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
            ['name' => 'Menu Nasi', 'description' => 'Aneka menu dengan nasi sebagai makanan pokok.'],
            ['name' => 'Aneka Mie', 'description' => 'Aneka masakan mie dan semacamnya.'],
            ['name' => 'Toping', 'description' => 'Tambahan toping untuk hidangan utama.'],
            ['name' => 'Minuman', 'description' => 'Minuman dingin dan hangat.'],
            ['name' => 'Camilan', 'description' => 'Cemilan dan makanan ringan.'],
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
            // Menu Nasi
            ['Nasi Goreng Nasicafe', 'Menu Nasi', 14000, 20, 5],
            ['Ayam Katsu', 'Menu Nasi', 20000, 15, 5],
            ['Nasi Ayam Penyet', 'Menu Nasi', 14000, 3, 5],   // low stock
            // Aneka Mie
            ['Mie Goreng Jawa', 'Aneka Mie', 14000, 12, 5],
            ['Mie Tek Tek', 'Aneka Mie', 14000, 2, 5],                 // low stock
            ['Indomie', 'Aneka Mie', 8000, 0, 5],                        // out of stock
            ['Bihun Ayam Kuah/Goreng', 'Aneka Mie', 14000, 8, 5],
            ['Spaghety', 'Aneka Mie', 10000, 10, 5],
            // Toping
            ['Telur Dadar', 'Toping', 3000, 45, 5],
            // Minuman
            ['Susu Jahe', 'Minuman', 7000, 25, 5],
            ['Chocolate Cloud', 'Minuman', 10000, 24, 5],
            ['Teh Manis', 'Minuman', 5000, 1, 5],                       // low stock
            ['Lemon Tea', 'Minuman', 7000, 10, 5],
            ['Teh Tarik', 'Minuman', 9000, 14, 5],
            ['Milo', 'Minuman', 9000, 19, 5],
            ['Teh Pucuk', 'Minuman', 5000, 15, 5],
            ['Air Mineral', 'Minuman', 5000, 30, 5],
            ["Kopi O'", 'Minuman', 5000, 10, 5],
            ['Kopi Susu', 'Minuman', 8000, 17, 5],
            ['Matcha Latte', 'Minuman', 10000, 25, 5],
            ['Matcha Frappe', 'Minuman', 10000, 21, 5],
            ['Cappucino', 'Minuman', 9000, 28, 5],
            ['Hot/Cold Chocolate', 'Minuman', 9000, 19, 5],
            ['Lychee Tea', 'Minuman', 7000, 13, 5],
            // Camilan Ringan
            ['Siomay', 'Camilan', 10000, 18, 5],
            ['French Fries', 'Camilan', 10000, 22, 5],
            ['Dimsum Ayam', 'Camilan', 10000, 13, 5],
            ['Dimsum Lumer', 'Camilan', 12000, 12, 5],
            ['Roti Bakar', 'Camilan', 10000, 6, 5],
            ['Kongkow Plater', 'Camilan', 15000, 10, 5],
            ['Bakwan Sayur', 'Camilan', 10000, 0, 5],
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
