<?php

namespace App\Services;

/**
 * Hash password HARUS identik dengan lib/utils/password_hasher.dart
 * (`sha256.convert(utf8.encode(password)).toString()`), yaitu:
 *
 *     hash('sha256', $password)
 *
 * JANGAN pakai `Hash::make()` / bcrypt — `verifyPassword()` di Dart
 * membandingkan string sha256 secara langsung, jadi format bcrypt akan
 * selalu gagal login.
 */
class PasswordHasher
{
    public static function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }

    /**
     * Mencerminkan logika DBHelper.login() versi SQLite:
     * terima sha256, DAN (untuk data lama) terima plaintext lalu upgrade.
     */
    public static function verify(string $plain, ?string $stored): bool
    {
        $stored = (string) $stored;

        if ($stored === '') {
            return false;
        }

        if (hash_equals($stored, self::hash($plain))) {
            return true;
        }

        // Password lama yang masih plaintext.
        return hash_equals($stored, $plain);
    }

    public static function needsUpgrade(?string $stored, string $plain): bool
    {
        return (string) $stored === $plain && ! hash_equals((string) $stored, self::hash($plain));
    }
}
