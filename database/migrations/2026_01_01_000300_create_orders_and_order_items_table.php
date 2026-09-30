<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * orders + order_items — 1:1 dengan SQLite lama.
 *
 * picked up:
 * - `pickupTime` tetap VARCHAR (bukan DATETIME) karena isinya "10:30" (jam saja).
 * - `createdAt` memakai DATETIME(3) agar bisa di agregasi dengan DATE_FORMAT(),
 *   dan ApiService meratakan object Carbon menjadi String ISO8601.
 * - Hanya `orderId` yang punya FK, sama seperti SQLite lama. `menuName` sengaja
 *   TIDAK diubah jadi FK karena query kategori menjadikannya lewat `menus.name`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId')->default(0);
            $table->string('orderCode', 50)->unique();
            $table->string('tenantName', 150);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('paymentMethod', 50);
            $table->string('paymentRecipient', 150)->default('Admin Smart Kantin');
            $table->string('paymentStatus', 50)->default('Menunggu Pembayaran');
            $table->string('status', 50);
            $table->string('pickupTime', 50);
            $table->text('note')->nullable();
            $table->dateTime('createdAt', 3);
            $table->string('guestName', 150)->nullable();
            $table->string('guestEmail', 191)->nullable();
            $table->string('phoneNumber', 30)->default('');
            $table->string('queueNumber', 10)->default('A01');
            $table->text('paymentLink')->nullable();
            $table->text('paymentQrPayload')->nullable();

            $table->index(['userId', 'id']);
            $table->index(['tenantName', 'id']);
            $table->index(['guestEmail', 'userId']);
            $table->index('createdAt');
            $table->index('status');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orderId');
            $table->string('menuName', 191);
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->index('orderId');

            $table->foreign('orderId')
                ->references('id')->on('orders')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
