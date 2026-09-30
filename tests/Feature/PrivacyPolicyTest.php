<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyPolicyTest extends TestCase
{
    public function test_privacy_policy_page_is_public(): void
    {
        $this->get('/privacy')
            ->assertOk()
            ->assertSee('Kebijakan Privasi')
            ->assertSee('Smart Kantin Kampus');
    }

    public function test_privacy_policy_states_data_is_stored_on_server(): void
    {
        $this->get('/privacy')
            ->assertOk()
            ->assertSee('disimpan di server')
            ->assertDontSee('SQLite');
    }
}
