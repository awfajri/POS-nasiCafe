<?php

namespace App\View\Components;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Throwable;

class LowStockBanner extends Component
{
    /**
     * Koleksi menu yang stoknya menipis (stock <= min_stock).
     *
     * @var Collection<int, Menu>
     */
    public Collection $lowStockMenus;

    /**
     * Jumlah menu yang stoknya menipis.
     */
    public int $count = 0;

    /**
     * Status apakah banner boleh ditampilkan (hanya untuk Owner dan Kasir).
     */
    public bool $shouldDisplay = false;

    /**
     * Inisialisasi komponen LowStockBanner.
     */
    public function __construct()
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Hanya tampil untuk peran Owner dan Kasir
        if ($user && ($user->isOwner() || $user->isCashier())) {
            try {
                $this->lowStockMenus = Menu::lowStock()->get();
                $this->count = $this->lowStockMenus->count();
                $this->shouldDisplay = $this->count > 0;
            } catch (Throwable $e) {
                $this->lowStockMenus = new Collection();
                $this->count = 0;
                $this->shouldDisplay = false;
            }
        } else {
            $this->lowStockMenus = new Collection();
            $this->count = 0;
            $this->shouldDisplay = false;
        }
    }

    /**
     * Tentukan apakah komponen harus dirender.
     */
    public function shouldRender(): bool
    {
        return $this->shouldDisplay;
    }

    /**
     * Render view komponen banner stok menipis.
     */
    public function render(): View|string
    {
        return view('components.low-stock-banner');
    }
}
