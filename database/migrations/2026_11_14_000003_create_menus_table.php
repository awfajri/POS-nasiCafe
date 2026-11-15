<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete(); // Relasi ke tabel categories; cegah hapus kategori jika masih ada menu terkait
            $table->string('name');
            $table->unsignedInteger('price');
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('min_stock')->default(5); // Batas minimum stok sebelum memunculkan peringatan stok menipis
            $table->string('image_path')->nullable(); // Lokasi/path foto menu, opsional (boleh null)
            $table->boolean('is_available')->default(true); // Status ketersediaan menu untuk dipesan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
