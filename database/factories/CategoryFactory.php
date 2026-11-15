<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    // isi default data kategori palsu
    public function definition(): array
    {
        $categories = [
            'Makanan Utama',
            'Makanan Ringan',
            'Minuman Dingin',
            'Minuman Hangat',
            'Aneka Nasi',
            'Aneka Mie',
            'Aneka Pasta',
            'Aneka Olahan Ayam',
            'Aneka Olahan Bebek',
            'Aneka Olahan Sapi',
            'Aneka Olahan Ikan',
            'Aneka Seafood',
            'Kopi Tradisional',
            'Kopi Espresso',
            'Manual Brew',
            'Non-Coffee',
            'Cokelat Klasik',
            'Teh Nusantara',
            'Artisan Tea',
            'Jus Buah Segar',
            'Smoothies & Shake',
            'Camilan Gurih',
            'Camilan Manis',
            'Kudapan Tradisional',
            'Dessert & Cake',
            'Pastry & Bakery',
            'Roti Bakar & Toast',
            'Paket Sarapan Pagi',
            'Paket Makan Siang',
            'Paket Makan Malam',
            'Paket Hemat Mahasiswa',
            'Paket Komplit Nasi Cafe',
            'Menu Spesial Chef',
            'Signature Drink',
            'Sambal Nusantara',
            'Aneka Gorengan',
            'Sup & Soto',
            'Sayur & Kuah',
            'Es Tradisional',
            'Es Buah & Campur',
            'Wedangan & Jamu',
            'Rice Bowl Spesial',
            'Bento Box Cafe',
            'Western Food',
            'Indonesian Heritage',
            'Dimsum & Siomay',
            'Bakso & Cuanki',
            'Burger & Hotdog',
            'Salad & Healthy Food',
            'Mocktail Segar',
        ];

        return [
            // nama dipilih acak, tapi ga dijalanin kalo name diisi manual
            'name' => fn () => fake()->unique()->randomElement($categories),
            // slug selalu dihitung dari name yang benar-benar dipakai
            'slug' => fn (array $attributes) => Str::slug($attributes['name']),
            'description' => fake()->sentence(6),
        ];
    }
}