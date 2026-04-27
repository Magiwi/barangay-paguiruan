<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    public function test_locale_switch_sets_session_and_redirects(): void
    {
        $response = $this->from('/')->get(route('locale.switch', ['locale' => 'fil']));

        $response->assertRedirect('/');
        $response->assertSessionHas('locale', 'fil');
    }

    public function test_invalid_locale_returns_not_found(): void
    {
        $this->get(route('locale.switch', ['locale' => 'xx']))->assertNotFound();
    }
}
