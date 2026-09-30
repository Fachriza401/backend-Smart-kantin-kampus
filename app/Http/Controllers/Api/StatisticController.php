<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Agregasi dipindahkan dari SQLite ke MySQL.
 * Query SQL-nya juga ditulis di docs/schema_mysql.sql (Flutter project).
 * NAMA KEY hasil wajib sama: rating/total, day/totalOrders/revenue, dll.
 */
class StatisticController extends Controller
{
    /**
     * GET /api/statistics/users
     * WAJIB mengirim KE-ENAM key walau 0 — dipakai DashboardStatsCarousel
     * untuk membuat diagram donat.
     */
    public function userStatistics(): JsonResponse
    {
        $counts = User::query()
            ->select('role', DB::raw('COUNT(*) AS total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $pick = fn (string $role): int => (int) ($counts[$role] ?? 0);

        $total = (int) $counts->sum();

        return response()->json([
            'total' => $total,
            'mahasiswa' => $pick('mahasiswa'),
            'dosen' => $pick('dosen'),
            'tenant' => $pick('tenant'),
            'kasir' => $pick('kasir'),
            'admin' => $pick('admin'),
        ]);
    }

    /** GET /api/statistics/ratings-summary */
    public function ratingsSummary(): JsonResponse
    {
        $rows = DB::table('ratings')
            ->select('rating', DB::raw('COUNT(*) AS total'))
            ->groupBy('rating')
            ->orderByDesc('rating')
            ->get()
            ->map(fn ($r) => [
                'rating' => (int) $r->rating,
                'total' => (int) $r->total,
            ]);

        return response()->json(['data' => $rows]);
    }

    /** GET /api/statistics/daily-sales?days=7&tenant_name= */
    public function dailySales(Request $request): JsonResponse
    {
        $days = max(1, min(90, (int) $request->query('days', 7)));
        $tenantName = $request->query('tenant_name');

        $query = DB::table('orders')
            ->selectRaw("DATE_FORMAT({$this->localCreatedAt()}, '%Y-%m-%d') AS day", [$this->reportOffset()])
            ->selectRaw('COUNT(*) AS totalOrders')
            ->selectRaw('COALESCE(SUM(total), 0) AS revenue')
            ->where('status', '!=', 'Dibatalkan');

        if ($tenantName) {
            $query->where('tenantName', $tenantName);
        }

        $rows = $query->groupBy('day')->orderByDesc('day')->limit($days)->get();

        return response()->json(['data' => $rows->map(fn ($r) => [
            'day' => (string) $r->day,
            'totalOrders' => (int) $r->totalOrders,
            'revenue' => (float) $r->revenue,
        ])]);
    }

    /** GET /api/statistics/monthly-sales?months=6&tenant_name= */
    public function monthlySales(Request $request): JsonResponse
    {
        $months = max(1, min(36, (int) $request->query('months', 6)));
        $tenantName = $request->query('tenant_name');

        $query = DB::table('orders')
            ->selectRaw("DATE_FORMAT({$this->localCreatedAt()}, '%Y-%m') AS month", [$this->reportOffset()])
            ->selectRaw('COUNT(*) AS totalOrders')
            ->selectRaw('COALESCE(SUM(total), 0) AS revenue')
            ->where('status', '!=', 'Dibatalkan');

        if ($tenantName) {
            $query->where('tenantName', $tenantName);
        }

        $rows = $query->groupBy('month')->orderByDesc('month')->limit($months)->get();

        return response()->json(['data' => $rows->map(fn ($r) => [
            'month' => (string) $r->month,
            'totalOrders' => (int) $r->totalOrders,
            'revenue' => (float) $r->revenue,
        ])]);
    }

    /**
     * `createdAt` disimpan dalam UTC; digeser ke jam lokal agar pesanan
     * pukul 00.00–07.00 WIB tidak terhitung di tanggal sebelumnya.
     * Pakai offset numerik supaya tidak butuh tabel zona waktu MySQL.
     */
    private function localCreatedAt(): string
    {
        return "CONVERT_TZ(createdAt, '+00:00', ?)";
    }

    private function reportOffset(): string
    {
        return (string) config('app.report_utc_offset', '+07:00');
    }

    /**
     * GET /api/statistics/menu-category-sales?tenant_name=
     * JOIN lewat `menus.name` (bukan menus.id) — sama seperti query SQLite lama.
     */
    public function menuCategorySales(Request $request): JsonResponse
    {
        $tenantName = $request->query('tenant_name');

        $query = DB::table('order_items')
            ->selectRaw("COALESCE(menus.category, 'Lainnya') AS category")
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) AS total')
            ->join('orders', 'orders.id', '=', 'order_items.orderId')
            ->leftJoin('menus', 'menus.name', '=', 'order_items.menuName')
            ->where('orders.status', '!=', 'Dibatalkan');

        if ($tenantName) {
            $query->where('orders.tenantName', $tenantName);
        }

        $rows = $query->groupBy('menus.category')->get();

        return response()->json(['data' => $rows->map(fn ($r) => [
            'category' => (string) $r->category,
            'total' => (int) $r->total,
        ])]);
    }
}
