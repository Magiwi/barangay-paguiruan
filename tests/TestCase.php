<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests use $this->put/post/delete without issuing a form token; disable CSRF in tests only.
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}
