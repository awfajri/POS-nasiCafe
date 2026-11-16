<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    // isi default data menu palsu
    public function definition(): array
    {
        $menus = [
            'Nasi Goreng Spesial',
            'Nasi Goreng Kampung',
            'Nasi Ayam Bakar Madu',
            'Nasi Ayam Geprek Sambal Bawang',
            'Nasi Ayam Goreng Lengkuas',
            'Nasi Bebek Goreng Crispy',
            'Nasi Gila NasiCafe',
            'Nasi Uduk Komplit',
            'Nasi Kuning Gurih',
            'Nasi Liwet Solo',
            'Mie Goreng Jawa',
            'Mie Godhog Spesial',
            'Kwetiau Goreng Seafood',
            'Bihun Goreng Ayam',
            'Kopi Susu Gula Aren',
            'Kopi Tubruk Manis',
            'Americano Iced',
            'Cafe Latte',
            'Cappuccino',
            'Matcha Latte',
            'Cokelat Klasik Dingin',
            'Teh Manis Dingin',
            'Lemon Tea Segar',
            'Lychee Tea',
            'Jus Alpukat',
            'Jus Mangga Segar',
            'Jus Jeruk Peras',
            'Roti Bakar Cokelat Keju',
            'Pisang Goreng Crispy',
            'Kentang Goreng Keju',
            'Cireng Bumbu Rujak',
            'Dimsum Ayam',
            'Tahu Crispy Cabe Garam',
            'Tempe Mendoan',
            'Singkong Goreng Keju',
        ];

        return [
            'category_id' => Category::factory(),
            'name' => fake()->randomElement($menus),
            'price' => fake()->numberBetween(10, 100) * 500,
            'stock' => fake()->numberBetween(0, 50),
            'min_stock' => 5,
            'image_path' => null,
            'is_available' => true,
        ];
    }

    // menu dengan stok menipis (stok <= min_stock)
    public function lowStock(?int $stock = null): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $stock ?? fake()->numberBetween(1, $attributes['min_stock'] ?? 5),
        ]);
    }

    // menu yang stoknya habis (stok = 0)
    public function outOfStock(): static
    {
        return $this->state(fn () => [
            'stock' => 0,
        ]);
    }
}