<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * notifications + topups + ratings + password_reset_requests + favorites
 * — 1:1 dengan SQLite lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('title', 191);
            $table->text('message');
            $table->string('type', 50);
            $table->tinyInteger('isRead')->default(0);
            $table->dateTime('createdAt', 3);
            $table->string('targetType', 50)->nullable();
            $table->unsignedBigInteger('targetId')->nullable();
            $table->index(['userId', 'isRead']);
        });

        Schema::create('topups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status', 30)->default('success');
            $table->dateTime('createdAt', 3);
            $table->index('userId');
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('menuId');
            $table->unsignedBigInteger('orderId');
            $table->unsignedTinyInteger('rating');
            $table->text('review')->nullable();
            $table->dateTime('createdAt', 3);
            // Menggantikan ConflictAlgorithm.replace di SQLite lama.
            $table->unique(['userId', 'menuId', 'orderId']);
            $table->index('menuId');
        });

        Schema::create('password_reset_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('role', 30);
            $table->string('status', 30)->default('pending');
            $table->dateTime('createdAt', 3);
            $table->index(['status', 'id']);
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('menuId');
            $table->dateTime('createdAt', 3);
            $table->primary(['userId', 'menuId']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('password_reset_requests');
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('topups');
        Schema::dropIfExists('notifications');
    }
};
