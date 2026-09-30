<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Base model untuk seluruh tabel Smart Kantin.
 *
 * Tiga hal WAJIB di sini:
 * 1. `public $timestamps = false` — tabel memakai `createdAt` (camelCase),
 *    bukan `created_at`/`updated_at` bawaan Laravel.
 * 2. `snakeAttributes = false` — kolom tetap camelCase, tidak di-convert.
 * 3. Tanpa `$casts` ke boolean — `tersedia` & `active` harus tetap int
 *    karena `MenuItem.fromMap` menulis `(map['tersedia'] as int?) == 1`.
 */
abstract class BaseModel extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    /**
     * Serialisasi tanggal selalu ISO8601 String, tidak pernah object Carbon.
     * Ini yang dibaca `map['createdAt'] as String` di sisi Dart.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:s.vP');
    }
}
