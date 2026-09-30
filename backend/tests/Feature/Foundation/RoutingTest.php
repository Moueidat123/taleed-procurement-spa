<?php

namespace Tests\Feature\Foundation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Same-origin route ownership: SPA shell, JSON API, CMS pages. */
class RoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_is_json_and_reports_database(): void
    {
        $this->getJson('/api/procurement/v1/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database', 'ok')
            ->assertHeader('X-Request-Id');
    }

    public function test_unknown_business_routes_return_the_json_error_envelope(): void
    {
        foreach (['/api/procurement/v1/does-not-exist', '/api/procurement/v2/anything', '/api/other'] as $uri) {
            $this->get($uri)
                ->assertNotFound()
                ->assertHeader('Content-Type', 'application/json')
                ->assertJsonPath('error.code', 'not_found')
                ->assertJsonStructure(['error' => ['code', 'message'], 'requestId']);
        }
    }

    public function test_protected_business_route_requires_application_identity(): void
    {
        $this->get('/api/procurement/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'unauthenticated');
    }

    public function test_root_serves_the_built_spa_shell_or_a_clear_503(): void
    {
        $response = $this->get('/');

        if (is_file(public_path('spa/index.html'))) {
            $response->assertOk()->assertSee('<div id="root"></div>', false)->assertHeader('X-Frame-Options', 'DENY');
        } else {
            $response->assertStatus(503);
        }
    }

    public function test_cms_privacy_placeholder_is_server_rendered_by_statamic(): void
    {
        $this->withoutVite();

        $this->get('/pages/privacy')
            ->assertOk()
            ->assertSee('Privacy notice')
            ->assertSee('Placeholder content — pending approval');
    }

    public function test_csrf_cookie_endpoint_is_reserved(): void
    {
        $this->get('/sanctum/csrf-cookie')->assertNoContent()->assertCookie('XSRF-TOKEN');
    }
}
