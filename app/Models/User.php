<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;

// Menambahkan role dan is_active
#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_OWNER = 'owner';
    public const ROLE_CASHIER = 'cashier';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLES = [self::ROLE_OWNER, self::ROLE_CASHIER, self::ROLE_CUSTOMER];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', 
            'is_active' => 'boolean',
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_OWNER => 'Pemilik',
            self::ROLE_CASHIER => 'Kasir',
            default => 'Pelanggan',
        };
    }

    public function dashboardUrl(): string
    {
        $routeName = match ($this->role) {
            self::ROLE_OWNER => 'owner.dashboard',
            self::ROLE_CASHIER => 'cashier.dashboard',
            default => 'customer.dashboard',
        };

        return Route::has($routeName) ? route($routeName) : url('/');
    }
}