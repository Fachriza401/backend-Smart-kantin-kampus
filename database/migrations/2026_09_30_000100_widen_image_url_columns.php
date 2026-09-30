<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foto menu/promo yang diunggah dari aplikasi disimpan sebagai data URL
 * base64 di `imageUrl`. TEXT MySQL hanya 64 KB sehingga foto dari galeri
 * ditolak ("Data too long"); MEDIUMTEXT menampung hingga 16 MB.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->mediumText('imageUrl')->nullable()->change();
        });

        Schema::table('promos', function (Blueprint $table) {
            $table->mediumText('imageUrl')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->text('imageUrl')->nullable()->change();
        });

        Schema::table('promos', function (Blueprint $table) {
            $table->text('imageUrl')->nullable()->change();
        });
    }
};
