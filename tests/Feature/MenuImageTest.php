<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_stores_and_returns_uploaded_base64_photo_larger_than_64kb(): void
    {
        // ~200 KB, lebih besar dari batas kolom TEXT MySQL (64 KB).
        $photo = 'data:image/jpeg;base64,'.str_repeat('A', 200_000);

        $id = $this->postJson('/api/menus', [
            'tenantId' => 1,
            'tenantName' => 'Kantin Kampus',
            'name' => 'Menu Foto Besar',
            'price' => 15000,
            'category' => 'Makanan',
            'imageUrl' => $photo,
        ])->assertCreated()->json('data.id');

        $menu = collect($this->getJson('/api/menus')->json('data'))
            ->firstWhere('id', $id);

        $this->assertSame($photo, $menu['imageUrl']);
    }
}
