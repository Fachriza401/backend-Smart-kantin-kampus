<?php

namespace Database\Seeders;

use App\Http\Controllers\Api\BootstrapController;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed akun admin/tenant/kasir + 40 menu + 3 promo.
     *
     * Memakai BootstrapController yang sama dengan endpoint `POST /api/bootstrap`,
     * jadi `php artisan migrate --seed` dan panggilan dari aplikasi Flutter
     * menghasilkan data yang identik. Keduanya idempoten.
     */
    public function run(): void
    {
        $response = app(BootstrapController::class)(Request::create('/api/bootstrap', 'POST'));

        $data = $response->getData(true)['data'] ?? [];

        $this->command?->info(sprintf(
            'Bootstrap selesai — user: %d, menu baru: %d, promo baru: %d (tenant: %s)',
            $data['users'] ?? 0,
            $data['menus'] ?? 0,
            $data['promos'] ?? 0,
            $data['tenant'] ?? '-',
        ));
    }
}
