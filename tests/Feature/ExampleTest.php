<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_public_catalog_returns_ok(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText(__('Tìm khách sạn'), false);
    }
}
