<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * menus + promos — 1:1 dengan SQLite lama.
 * `tersedia` & `active` sengaja TINYINT (bukan boolean) karena
 * MenuItem.fromMap menulis `(map['tersedia'] as int?) == 1`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenantId')->default(1);
            $table->string('tenantName', 150)->default('Kantin Kampus');
            $table->string('name', 191);
            $table->decimal('price', 12, 2)->default(0);
            $table->string('category', 100);
            $table->text('description')->nullable();
            $table->double('rating')->default(0);
            $table->unsignedInteger('reviewCount')->default(0);
            $table->string('estimasi', 50)->nullable();
            $table->tinyInteger('tersedia')->default(1);
            $table->string('icon', 50)->nullable();
            $table->text('imageUrl')->nullable();
            $table->index('category');
            $table->index('tenantId');
            $table->index('name');
        });

        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->text('description');
            $table->decimal('discount', 5, 2)->default(0);
            $table->tinyInteger('active')->default(1);
            $table->string('icon', 50)->default('local_offer');
            $table->text('imageUrl')->nullable();
            $table->string('startDate', 50)->nullable();
            $table->string('endDate', 50)->nullable();
            $table->index(['active', 'discount']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promos');
        Schema::dropIfExists('menus');
    }
};
