<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

// STUB (Fase 2 bagian 1). Isi dikerjakan di bagian 2.
class UserController extends Controller
{
    public function index(Request $request): never
    {
        abort(501, 'Belum diimplementasi');
    }

    public function create(): never
    {
        abort(501, 'Belum diimplementasi');
    }

    public function store(StoreUserRequest $request): never
    {
        abort(501, 'Belum diimplementasi');
    }

    public function edit(User $user): never
    {
        abort(501, 'Belum diimplementasi');
    }

    public function update(UpdateUserRequest $request, User $user): never
    {
        abort(501, 'Belum diimplementasi');
    }

    public function toggleActive(User $user): never
    {
        abort(501, 'Belum diimplementasi');
    }
}