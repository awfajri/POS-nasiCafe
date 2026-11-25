<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $cashier;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->owner = User::factory()->owner()->create();
        $this->cashier = User::factory()->cashier()->create();
        $this->customer = User::factory()->customer()->create();
    }

    // =========================================================================
    // 1. PENGUJIAN HAK AKSES ROLE (OWNER, CASHIER, CUSTOMER)
    // =========================================================================

    public function test_kasir_dan_pelanggan_tidak_dapat_mengakses_halaman_master_data(): void
    {
        $this->actingAs($this->cashier)->get(route('owner.categories.index'))->assertForbidden();
        $this->actingAs($this->cashier)->get(route('owner.menus.index'))->assertForbidden();

        $this->actingAs($this->customer)->get(route('owner.categories.index'))->assertForbidden();
        $this->actingAs($this->customer)->get(route('owner.menus.index'))->assertForbidden();
    }

    public function test_owner_dapat_mengakses_halaman_master_data(): void
    {
        $this->actingAs($this->owner)->get(route('owner.categories.index'))->assertOk();
        $this->actingAs($this->owner)->get(route('owner.menus.index'))->assertOk();
    }

    // =========================================================================
    // 2. PENGUJIAN CRUD KATEGORI
    // =========================================================================

    public function test_owner_bisa_menambah_kategori(): void
    {
        $payload = [
            'name' => 'Minuman Dingin',
            'description' => 'Kategori aneka minuman segar dan dingin.',
        ];

        $response = $this->actingAs($this->owner)->post(route('owner.categories.store'), $payload);

        $response->assertRedirect(route('owner.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Minuman Dingin',
            'slug' => 'minuman-dingin',
            'description' => 'Kategori aneka minuman segar dan dingin.',
        ]);
    }

    public function test_owner_bisa_mengubah_kategori(): void
    {
        $category = Category::factory()->create([
            'name' => 'Makanan Ringan',
            'slug' => 'makanan-ringan',
        ]);

        $payload = [
            'name' => 'Camilan Gurih',
            'description' => 'Deskripsi diperbarui.',
        ];

        $response = $this->actingAs($this->owner)->put(route('owner.categories.update', $category), $payload);

        $response->assertRedirect(route('owner.categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Camilan Gurih',
            'slug' => 'camilan-gurih',
            'description' => 'Deskripsi diperbarui.',
        ]);
    }

    public function test_owner_bisa_menghapus_kategori_kosong(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->owner)->delete(route('owner.categories.destroy', $category));

        $response->assertRedirect(route('owner.categories.index'));
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_hapus_kategori_ditolak_jika_masih_memiliki_menu(): void
    {
        $category = Category::factory()->create();
        Menu::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->owner)->delete(route('owner.categories.destroy', $category));

        $response->assertSessionHasErrors('category');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    // =========================================================================
    // 3. PENGUJIAN CRUD MENU & UPLOAD FOTO
    // =========================================================================

    public function test_owner_bisa_menambah_menu_dengan_foto_valid(): void
    {
        Storage::fake('public');

        $category = Category::factory()->create();
        $file = UploadedFile::fake()->image('ayam-bakar.jpg', 600, 600)->size(1500);

        $payload = [
            'category_id' => $category->id,
            'name' => 'Ayam Bakar Madu',
            'price' => 28000,
            'stock' => 25,
            'min_stock' => 5,
            'is_available' => 1,
            'image' => $file,
        ];

        $response = $this->actingAs($this->owner)->post(route('owner.menus.store'), $payload);

        $response->assertRedirect(route('owner.menus.index'));

        $menu = Menu::where('name', 'Ayam Bakar Madu')->firstOrFail();
        $this->assertNotNull($menu->image_path);
        $this->assertStringStartsWith('menus/', $menu->image_path);
        $this->assertNotSame('ayam-bakar.jpg', basename($menu->image_path));

        Storage::disk('public')->assertExists($menu->image_path);
        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'name' => 'Ayam Bakar Madu',
            'price' => 28000,
            'image_path' => $menu->image_path,
        ]);
    }

    // =========================================================================
    // 4. PENGUJIAN VALIDASI UPLOAD FOTO
    // =========================================================================

    public function test_upload_file_non_gambar_ditolak_validasi(): void
    {
        Storage::fake('public');

        $category = Category::factory()->create();
        $nonImage = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        $payload = [
            'category_id' => $category->id,
            'name' => 'Menu Uji Non Gambar',
            'price' => 15000,
            'stock' => 10,
            'min_stock' => 5,
            'is_available' => 1,
            'image' => $nonImage,
        ];

        $response = $this->actingAs($this->owner)->post(route('owner.menus.store'), $payload);

        $response->assertSessionHasErrors('image');
        $this->assertDatabaseMissing('menus', ['name' => 'Menu Uji Non Gambar']);
    }

    public function test_upload_gambar_melebihi_2mb_ditolak_validasi(): void
    {
        Storage::fake('public');

        $category = Category::factory()->create();
        $oversizedImage = UploadedFile::fake()->image('gambar-besar.jpg')->size(2500);

        $payload = [
            'category_id' => $category->id,
            'name' => 'Menu Uji Gambar Besar',
            'price' => 15000,
            'stock' => 10,
            'min_stock' => 5,
            'is_available' => 1,
            'image' => $oversizedImage,
        ];

        $response = $this->actingAs($this->owner)->post(route('owner.menus.store'), $payload);

        $response->assertSessionHasErrors('image');
        $this->assertDatabaseMissing('menus', ['name' => 'Menu Uji Gambar Besar']);
    }

    // =========================================================================
    // 5. PENGUJIAN UPDATE FOTO (HAPUS FOTO LAMA)
    // =========================================================================

    public function test_foto_lama_terhapus_dari_disk_public_saat_menu_diperbarui_dengan_foto_baru(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('foto-lama.jpg');
        $oldPath = $oldFile->store('menus', 'public');

        $menu = Menu::factory()->create(['image_path' => $oldPath]);
        Storage::disk('public')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->image('foto-baru.png');

        $payload = [
            'category_id' => $menu->category_id,
            'name' => $menu->name,
            'price' => $menu->price,
            'stock' => $menu->stock,
            'min_stock' => $menu->min_stock,
            'is_available' => 1,
            'image' => $newFile,
        ];

        $response = $this->actingAs($this->owner)->put(route('owner.menus.update', $menu), $payload);

        $response->assertRedirect(route('owner.menus.index'));

        $menu->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($menu->image_path);
        $this->assertNotSame($oldPath, $menu->image_path);
    }

    // =========================================================================
    // 6. PENGUJIAN PROTEKSI HAPUS MENU
    // =========================================================================

    public function test_menu_yang_belum_pernah_dipakai_di_transaksi_bisa_dihapus_beserta_fotonya(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('foto-menu.jpg');
        $path = $file->store('menus', 'public');

        $menu = Menu::factory()->create(['image_path' => $path]);
        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($this->owner)->delete(route('owner.menus.destroy', $menu));

        $response->assertRedirect(route('owner.menus.index'));
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_menu_yang_sudah_pernah_dipakai_di_transaksi_ditolak_penghapusannya(): void
    {
        $menu = Menu::factory()->create();
        TransactionDetail::factory()->create(['menu_id' => $menu->id]);

        $response = $this->actingAs($this->owner)->delete(route('owner.menus.destroy', $menu));

        $response->assertSessionHasErrors('menu');
        $this->assertDatabaseHas('menus', ['id' => $menu->id]);
    }

    // =========================================================================
    // 7. PENGUJIAN QUICK RESTOCK
    // =========================================================================

    public function test_quick_restock_aksi_add_menambah_stok(): void
    {
        $menu = Menu::factory()->create(['stock' => 10]);

        $response = $this->actingAs($this->owner)->patch(route('owner.menus.stock', $menu), [
            'stock_action' => 'add',
            'amount' => 15,
        ]);

        $response->assertSessionHasNoErrors();
        $menu->refresh();
        $this->assertSame(25, $menu->stock);
    }

    public function test_quick_restock_aksi_set_mengatur_stok_langsung(): void
    {
        $menu = Menu::factory()->create(['stock' => 10]);

        $response = $this->actingAs($this->owner)->patch(route('owner.menus.stock', $menu), [
            'stock_action' => 'set',
            'amount' => 50,
        ]);

        $response->assertSessionHasNoErrors();
        $menu->refresh();
        $this->assertSame(50, $menu->stock);
    }

    public function test_quick_restock_aksi_add_yang_membuat_total_stok_melebihi_100000_ditolak(): void
    {
        $menu = Menu::factory()->create(['stock' => 99990]);

        $response = $this->actingAs($this->owner)->patch(route('owner.menus.stock', $menu), [
            'stock_action' => 'add',
            'amount' => 20,
        ]);

        $response->assertSessionHasErrors('amount');
        $menu->refresh();
        $this->assertSame(99990, $menu->stock);
    }
}