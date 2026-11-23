<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerOrderRequest;
use App\Models\Category;
use App\Models\Menu;
use App\Services\TransactionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CustomerOrderController extends Controller
{
    /**
     * Tampilkan katalog menu interaktif untuk Self-Order pelanggan.
     */
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->withCount(['menus' => function ($query) {
                $query->where('is_available', true);
            }])
            ->orderBy('name')
            ->get();

        $menus = Menu::query()
            ->with('category')
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        return view('customer.order', compact('categories', 'menus'));
    }

    /**
     * Simpan pesanan self-order dari pelanggan.
     */
    public function store(StoreCustomerOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $transaction = app(TransactionService::class)->createOrder(
            $validated['items'],
            $request->user()->name,
            $validated['table_number'] ?? null,
            'self_order',
            $request->user()
        );

        $redirect = Route::has('customer.dashboard')
            ? redirect()->route('customer.dashboard')
            : redirect('/customer/dashboard');

        return $redirect->with('success', "Pesanan berhasil dibuat! Kode nota: {$transaction->invoice_code}");
    }
}