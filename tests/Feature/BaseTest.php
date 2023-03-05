<?php

namespace Tests\Feature;

use Tests\TestCase;

class BaseTest extends TestCase
{
    public function test_the_central_application_returns_a_successful_response()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
