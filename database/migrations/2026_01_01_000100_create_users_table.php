<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * users — 1:1 dengan SQLite lama.
 * Kolom tetap camelCase karena lib/models/user.dart memakai nama itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 191)->unique();
            $table->string('password', 191);
            $table->string('role', 30)->default('mahasiswa');
            $table->string('nirm', 50)->nullable()->unique();
            $table->text('photoPath')->nullable();
            $table->decimal('saldo', 12, 2)->default(0);
            $table->string('tenantName', 150)->nullable();
            $table->index(['tenantName', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
