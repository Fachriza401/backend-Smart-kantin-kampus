<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Tabel aplikasi memakai kolom tanggal camelCase (createdAt), bukan
         * created_at, dan modelnya tidak mengaktifkan $timestamps. Blok ini
         * memastikan setiap Carbon yang masuk JSON menjadi String ISO8601 —
         * persis yang diharapkan AppUser.fromMap() / Order.fromMap() di Dart.
         */
        Carbon::serializeUsing(function (Carbon $date): string {
            return $date->toIso8601String();
        });
    }
}
