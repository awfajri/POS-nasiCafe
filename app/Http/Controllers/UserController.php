<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    private const ROLE_LABELS = [
        User::ROLE_OWNER => 'Pemilik',
        User::ROLE_CASHIER => 'Kasir',
        User::ROLE_CUSTOMER => 'Pelanggan',
    ];

    public function index(Request $request): View
    {
        // Nilai query string bisa berupa array (?search[]=x), jadi dicek tipenya dulu.
        $search = $request->query('search');
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';

        $role = $request->query('role');
        $role = in_array($role, User::ROLES, true) ? $role : null;

        $users = User::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($role !== null, fn ($query) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('owner.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'roleLabels' => self::ROLE_LABELS,
        ]);
    }

    public function create(): View
    {
        return view('owner.users.form', [
            'user' => new User(),
            'isSelf' => false,
            'roles' => User::ROLES,
            'roleLabels' => self::ROLE_LABELS,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = new User(Arr::only($data, ['name', 'email', 'password']));
        $user->role = $data['role']; // diset eksplisit karena role bukan fillable
        $user->is_active = true;     // diset eksplisit, tidak pernah dari request
        $user->save();

        return redirect()->route('owner.users.index')
            ->with('success', "Akun {$user->name} berhasil dibuat.");
    }

    public function edit(User $user): View
    {
        return view('owner.users.form', [
            'user' => $user,
            'isSelf' => $user->is(auth()->user()),
            'roles' => User::ROLES,
            'roleLabels' => self::ROLE_LABELS,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // Owner tidak boleh mengubah peran akunnya sendiri (supaya tidak terkunci).
        if ($user->is($request->user()) && $data['role'] !== $user->role) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak dapat mengubah peran akun Anda sendiri.',
            ]);
        }

        $user->fill(Arr::only($data, ['name', 'email']));
        $user->role = $data['role'];

        // Password hanya diubah kalau diisi. Cast hashed yang melakukan hash, jangan hash dua kali.
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return redirect()->route('owner.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        // Owner tidak boleh menonaktifkan dirinya sendiri.
        if ($user->is(auth()->user())) {
            throw ValidationException::withMessages([
                'user' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.',
            ]);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('success', $user->is_active
            ? "Akun {$user->name} diaktifkan."
            : "Akun {$user->name} dinonaktifkan.");
    }
}