<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PasswordHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_demo_accounts_menus_and_promos_on_empty_database(): void
    {
        $this->postJson('/api/bootstrap')->assertOk();

        $this->assertSame(4, User::count());
        $this->assertSame(
            PasswordHasher::hash('admin123'),
            User::where('email', 'admin@kantin.app')->value('password'),
        );
        $this->getJson('/api/menus')->assertOk()->assertJsonCount(40, 'data');
        $this->getJson('/api/promos')->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_does_not_reset_password_changed_after_first_bootstrap(): void
    {
        $this->postJson('/api/bootstrap')->assertOk();
        $kasir = User::where('email', 'kasir@kantin.app')->firstOrFail();
        $newHash = PasswordHasher::hash('passwordBaru123');
        $this->putJson("/api/users/{$kasir->id}/password", ['password' => $newHash])
            ->assertOk();

        $this->postJson('/api/bootstrap')->assertOk();

        $this->assertSame($newHash, $kasir->fresh()->password);
        $this->postJson('/api/auth/login', [
            'identifier' => 'kasir@kantin.app',
            'password' => 'passwordBaru123',
        ])->assertOk();
    }

    public function test_does_not_reset_student_balance_on_every_launch(): void
    {
        $this->postJson('/api/bootstrap')->assertOk();
        $student = User::where('email', 'fachriza@kantin.app')->firstOrFail();
        $student->update(['saldo' => 25000]);

        $this->postJson('/api/bootstrap')->assertOk();

        $this->assertEquals(25000, $student->fresh()->saldo);
    }

    public function test_still_normalizes_staff_tenant_name(): void
    {
        $this->postJson('/api/bootstrap')->assertOk();
        User::where('email', 'kasir@kantin.app')->update(['tenantName' => 'Salah']);

        $this->postJson('/api/bootstrap')->assertOk();

        $this->assertSame(
            'Kantin Kampus',
            User::where('email', 'kasir@kantin.app')->value('tenantName'),
        );
    }
}
