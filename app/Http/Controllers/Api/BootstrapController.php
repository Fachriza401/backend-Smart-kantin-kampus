<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Promo;
use App\Models\User;
use App\Services\PasswordHasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * POST /api/bootstrap
 *
 * Dipanggil splash screen. Menggantikan seluruh seeding yang dulu ada di
 * DBHelper._seedInitialData() / _onCreate() versi SQLite.
 * WAJIB idempoten: aman dipanggil setiap kali aplikasi dibuka.
 */
class BootstrapController extends Controller
{
    public const TENANT_NAME = 'Kantin Kampus';

    public function __invoke(): JsonResponse
    {
        $this->upsertUser([
            'name' => 'Administrator',
            'email' => 'admin@kantin.app',
            'password' => PasswordHasher::hash('admin123'),
            'role' => 'admin',
            'nirm' => null,
            'photoPath' => null,
            'saldo' => 0,
            'tenantName' => null,
        ]);

        $tenant = $this->upsertUser([
            'name' => self::TENANT_NAME,
            'email' => 'tenant1@kantin.app',
            'password' => PasswordHasher::hash('tenant123'),
            'role' => 'tenant',
            'nirm' => null,
            'photoPath' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=900&q=80',
            'saldo' => 0,
            'tenantName' => self::TENANT_NAME,
        ]);

        $this->upsertUser([
            'name' => 'Kasir Kantin Kampus',
            'email' => 'kasir@kantin.app',
            'password' => PasswordHasher::hash('kasir123'),
            'role' => 'kasir',
            'nirm' => null,
            'photoPath' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=900&q=80',
            'saldo' => 0,
            'tenantName' => self::TENANT_NAME,
        ]);

        $this->upsertUser([
            'name' => 'Fachriza',
            'email' => 'fachriza@kantin.app',
            'password' => PasswordHasher::hash('fachriza123'),
            'role' => 'mahasiswa',
            'nirm' => 'FACHRIZA001',
            'photoPath' => null,
            'saldo' => 100000,
            'tenantName' => null,
        ]);

        $menus = $this->seedMenus((int) $tenant->id);
        $promos = $this->seedPromos();

        return response()->json([
            'data' => [
                'ok' => true,
                'users' => (int) User::count(),
                'menus' => $menus,
                'promos' => $promos,
                'tenant' => self::TENANT_NAME,
            ],
        ]);
    }

    /**
     * `password` & `saldo` hanya diisi saat akun pertama kali dibuat.
     * Endpoint ini dipanggil setiap aplikasi dibuka, jadi menimpa keduanya
     * akan membatalkan ganti password / reset password dan saldo pengguna.
     */
    private const CREATE_ONLY_FIELDS = ['password', 'saldo'];

    private function upsertUser(array $data): User
    {
        $existing = User::where('email', $data['email'])->first();

        if ($existing) {
            $existing->fill(Arr::except($data, self::CREATE_ONLY_FIELDS))->save();

            return $existing;
        }

        return User::create($data);
    }

    private function seedMenus(int $tenantId): int
    {
        if (Menu::count() > 0) {
            return 0;
        }

        $rows = [];

        foreach (self::menuCatalog() as $item) {
            $rows[] = $item + [
                'tenantId' => $tenantId,
                'tenantName' => self::TENANT_NAME,
                'rating' => 0,
                'reviewCount' => 0,
                'estimasi' => '10-15 menit',
                'tersedia' => 1,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('menus')->insert($chunk);
        }

        return count($rows);
    }

    private function seedPromos(): int
    {
        if (Promo::count() > 0) {
            return 0;
        }

        DB::table('promos')->insert([
            [
                'title' => 'Diskon Spesial Hari Ini',
                'description' => 'Diskon sampai 30% untuk menu pilihan.',
                'discount' => 30.00,
                'active' => 1,
                'icon' => 'local_offer',
                'imageUrl' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=1200&q=80',
                'startDate' => null,
                'endDate' => null,
            ],
            [
                'title' => 'Promo Paket Hemat',
                'description' => 'Paket makanan + minuman lebih murah.',
                'discount' => 20.00,
                'active' => 1,
                'icon' => 'fastfood',
                'imageUrl' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?auto=format&fit=crop&w=1200&q=80',
                'startDate' => null,
                'endDate' => null,
            ],
            [
                'title' => 'Cashback Saldo',
                'description' => 'Cashback 10% untuk pembayaran saldo kampus.',
                'discount' => 10.00,
                'active' => 1,
                'icon' => 'account_balance_wallet',
                'imageUrl' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1200&q=80',
                'startDate' => null,
                'endDate' => null,
            ],
        ]);

        return 3;
    }

    /**
     * Katalog menu. `imageUrl` memakai URL Unsplash supaya tidak perlu
     * mengunggah aset Flutter ke server.
     */
    private static function menuCatalog(): array
    {
        $img = fn (string $id): string =>
            "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=900&q=80";

        return [
            ['name' => 'Nasi Goreng Spesial', 'price' => 25000, 'category' => 'Makanan', 'description' => 'Nasi goreng dengan ayam, telur, dan acar.', 'icon' => 'restaurant', 'imageUrl' => $img('1512058564366-18510be2db19')],
            ['name' => 'Mie Ayam Bakso', 'price' => 22000, 'category' => 'Makanan', 'description' => 'Mie ayam dengan bakso sapi.', 'icon' => 'ramen_dining', 'imageUrl' => $img('1569718212165-3a8278d5f624')],
            ['name' => 'Ayam Penyet', 'price' => 28000, 'category' => 'Makanan', 'description' => 'Ayam penyet sambal matah.', 'icon' => 'kebab_dining', 'imageUrl' => $img('1604908176997-125f25cc6f3d')],
            ['name' => 'Nasi Ayam Kecap', 'price' => 24000, 'category' => 'Makanan', 'description' => 'Nasi putih dengan ayam kecap.', 'icon' => 'dinner_dining', 'imageUrl' => $img('1512621776951-a57141f2eefd')],
            ['name' => 'Soto Ayam', 'price' => 20000, 'category' => 'Makanan', 'description' => 'Soto ayam bening khas kampus.', 'icon' => 'soup_kitchen', 'imageUrl' => $img('1547592166-23ac45744acd')],
            ['name' => 'Gado-Gado', 'price' => 18000, 'category' => 'Makanan', 'description' => 'Sayur campur Peanut sauce.', 'icon' => 'eco', 'imageUrl' => $img('1512621776951-a57141f2eefd')],
            ['name' => 'Nasi Ulang', 'price' => 21000, 'category' => 'Makanan', 'description' => 'Nasi goreng crab dengan saus khas.', 'icon' => 'lunch_dining', 'imageUrl' => $img('1559314809-0d155014e29e')],
            ['name' => 'Rendang Ketan', 'price' => 27000, 'category' => 'Makanan', 'description' => 'Nasi ketan dengan rendang.', 'icon' => 'brunch_dining', 'imageUrl' => $img('1512058564366-18510be2db19')],
            ['name' => 'Iga Bakar', 'price' => 32000, 'category' => 'Makanan', 'description' => 'Iga bakar sauce barbecue.', 'icon' => 'outdoor_grill', 'imageUrl' => $img('1544025162-d76694265947')],
            ['name' => 'Mie Goreng Spesial', 'price' => 23000, 'category' => 'Makanan', 'description' => 'Mie goreng dengan seafood.', 'icon' => 'ramen_dining', 'imageUrl' => $img('1569718212165-3a8278d5f624')],

            ['name' => 'Es Teh Manis', 'price' => 5000, 'category' => 'Minuman', 'description' => 'Teh manis dingin.', 'icon' => 'local_cafe', 'imageUrl' => $img('1544145945-f90425340c7e')],
            ['name' => 'Kopi Susu Gula Aren', 'price' => 18000, 'category' => 'Minuman', 'description' => 'Kopi susu aren premium.', 'icon' => 'coffee', 'imageUrl' => $img('1461023058943-07fcbe16d735')],
            ['name' => 'Jus Alpukat', 'price' => 16000, 'category' => 'Minuman', 'description' => 'Jus alpukat asli.', 'icon' => 'local_drink', 'imageUrl' => $img('1621506289937-a8e4df5d0f5b')],
            ['name' => 'Es Jeruk Peras', 'price' => 12000, 'category' => 'Minuman', 'description' => 'Jeruk peras segar.', 'icon' => 'emoji_food_beverage', 'imageUrl' => $img('1621506289937-a8e4df5d0f5b')],
            ['name' => 'Air Mineral 600ml', 'price' => 4000, 'category' => 'Minuman', 'description' => 'Air mineral dingin.', 'icon' => 'water_drop', 'imageUrl' => $img('1523362628745-0c100150b504')],
            ['name' => 'Kopi Susu Botol', 'price' => 10000, 'category' => 'Minuman', 'description' => 'Kopi susu kemasan.', 'icon' => 'coffee', 'imageUrl' => $img('1578314675249-a6910f80cc4e')],
            ['name' => 'Matcha Latte', 'price' => 22000, 'category' => 'Minuman', 'description' => 'Matcha latte creamy.', 'icon' => 'coffee', 'imageUrl' => $img('1515823064-d6e0c04616a7')],
            ['name' => 'Es Kapal Selir', 'price' => 8000, 'category' => 'Minuman', 'description' => 'Es kelapa dengan gula aren.', 'icon' => 'emoji_nature', 'imageUrl' => $img('1544145945-f90425340c7e')],
            ['name' => 'Susu Cokelat', 'price' => 14000, 'category' => 'Minuman', 'description' => 'Susu cokelat hangat.', 'icon' => 'local_cafe', 'imageUrl' => $img('1542990253-0d0f5be5f0ed')],
            ['name' => 'Lemon Tea', 'price' => 11000, 'category' => 'Minuman', 'description' => 'Teh lemon segar.', 'icon' => 'emoji_food_beverage', 'imageUrl' => $img('1556679343-c7306c1976bc')],

            ['name' => 'Keripik Kentang', 'price' => 12000, 'category' => 'Snacks', 'description' => 'Keripik kentang renyah.', 'icon' => 'fastfood', 'imageUrl' => $img('1566478989037-eec170784d0b')],
            ['name' => 'Roti Bakar Cokelat', 'price' => 13000, 'category' => 'Snacks', 'description' => 'Roti bakar isi cokelat.', 'icon' => 'bakery_dining', 'imageUrl' => $img('1509440159596-0249088772ff')],
            ['name' => 'Rujak Mangga', 'price' => 15000, 'category' => 'Snacks', 'description' => 'Rujak mangga segar.', 'icon' => 'eco', 'imageUrl' => $img('1512621776951-a57141f2eefd')],
            ['name' => 'Kroepoek Telur', 'price' => 10000, 'category' => 'Snacks', 'description' => 'Kroepoek telur crispy.', 'icon' => 'breakfast_dining', 'imageUrl' => $img('1608039829572-78524f79c4c7')],
            ['name' => 'Chitato', 'price' => 11000, 'category' => 'Snacks', 'description' => 'Kentang goreng Chitato.', 'icon' => 'fastfood', 'imageUrl' => $img('1573080496219-bb080dd4f877')],
            ['name' => 'Pisang Goreng', 'price' => 9000, 'category' => 'Snacks', 'description' => 'Pisang goreng sugar.', 'icon' => 'icecream', 'imageUrl' => $img('1571771894821-ce9b6c11b08e')],
            ['name' => 'Kue Lapis', 'price' => 8000, 'category' => 'Snacks', 'description' => 'Kue lapis legit.', 'icon' => 'cake', 'imageUrl' => $img('1578985545062-69928b1d9587')],
            ['name' => 'Donat Gula', 'price' => 10000, 'category' => 'Snacks', 'description' => 'Donat bergula manis.', 'icon' => 'bakery_dining', 'imageUrl' => $img('1551024506-0bccd828d307')],
            ['name' => 'Kerupuk Udang', 'price' => 7000, 'category' => 'Snacks', 'description' => 'Kerupuk udang renyah.', 'icon' => 'set_meal', 'imageUrl' => $img('1566478989037-eec170784d0b')],
            ['name' => 'Chips Kentang', 'price' => 12000, 'category' => 'Snacks', 'description' => 'Chips kentang original.', 'icon' => 'fastfood', 'imageUrl' => $img('1573080496219-bb080dd4f877')],

            ['name' => 'Nasi Putih', 'price' => 4000, 'category' => 'Aneka', 'description' => 'Nasi putih hangat.', 'icon' => 'rice_bowl', 'imageUrl' => $img('1512058564366-18510be2db19')],
            ['name' => 'Sup Ayam', 'price' => 15000, 'category' => 'Aneka', 'description' => 'Sup ayam bening.', 'icon' => 'soup_kitchen', 'imageUrl' => $img('1547592166-23ac45744acd')],
            ['name' => 'Sayur Asem', 'price' => 13000, 'category' => 'Aneka', 'description' => 'Sayur asem Jawa.', 'icon' => 'eco', 'imageUrl' => $img('1512621776951-a57141f2eefd')],
            ['name' => 'Telur Balado', 'price' => 17000, 'category' => 'Aneka', 'description' => 'Telur balado sambal.', 'icon' => 'egg_alt', 'imageUrl' => $img('1608039829572-78524f79c4c7')],
            ['name' => 'Tempe Mendoan', 'price' => 8000, 'category' => 'Aneka', 'description' => 'Tempe mendoan goreng.', 'icon' => 'set_meal', 'imageUrl' => $img('1608039829572-78524f79c4c7')],
            ['name' => 'Perkedel Kentang', 'price' => 9000, 'category' => 'Aneka', 'description' => 'Perkedel kentang goreng.', 'icon' => 'lunch_dining', 'imageUrl' => $img('1559314809-0d155014e29e')],
            ['name' => 'Bika Ambon', 'price' => 16000, 'category' => 'Aneka', 'description' => 'Kue bika ambon lembut.', 'icon' => 'cake', 'imageUrl' => $img('1578985545062-69928b1d9587')],
            ['name' => 'Es Dauwut', 'price' => 12000, 'category' => 'Aneka', 'description' => 'Es dawut kelapa muda.', 'icon' => 'icecream', 'imageUrl' => $img('1621506289937-a8e4df5d0f5b')],
            ['name' => 'Nasi Liwet', 'price' => 19000, 'category' => 'Aneka', 'description' => 'Nasi liwet khas Jawa.', 'icon' => 'rice_bowl', 'imageUrl' => $img('1512058564366-18510be2db19')],
            ['name' => 'Sayur Bean', 'price' => 11000, 'category' => 'Aneka', 'description' => 'Sayur kacang hijau.', 'icon' => 'eco', 'imageUrl' => $img('1512621776951-a57141f2eefd')],
        ];
    }
}
