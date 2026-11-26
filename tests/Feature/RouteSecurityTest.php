<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_semua_rute_memakai_guest_atau_auth_dengan_role(): void
    {
        $melanggar = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            $name = (string) $route->getName();

            // Lewati rute bawaan framework dan halaman publik yang disepakati
            // ('/' hanya redirect ke login atau dasbor).
            if (
                $uri === '/'
                || $uri === 'up'
                || str_starts_with($uri, '_')
                || str_starts_with($name, 'storage.local')
            ) {
                continue;
            }

            $middleware = $route->gatherMiddleware();

            if (in_array('guest', $middleware, true)) {
                continue;
            }

            $punyaAuth = in_array('auth', $middleware, true);
            $punyaRole = collect($middleware)->contains(
                fn ($m) => is_string($m) && str_starts_with($m, 'role:')
            );

            if (! $punyaAuth || ! $punyaRole) {
                $melanggar[] = implode('|', $route->methods())." /{$uri} ({$name})";
            }
        }

        $this->assertSame([], $melanggar, "Rute tanpa guest atau auth + role:\n".implode("\n", $melanggar));
    }
}