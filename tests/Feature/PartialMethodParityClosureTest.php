<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class PartialMethodParityClosureTest extends TestCase
{
    /**
     * @dataProvider endpointsProvider
     */
    public function test_get_method_parity_routes_are_resolvable(string $method, string $uri): void
    {
        $router = app('router');
        $request = Request::create($uri, $method);

        try {
            $route = $router->getRoutes()->match($request);
        } catch (NotFoundHttpException $e) {
            $this->fail("Route not found for {$method} {$uri}");
            return;
        }

        $this->assertNotNull($route);
    }

    public static function endpointsProvider(): array
    {
        return [
            ['GET', '/billing/fingerprint?month_cycle=03-2026'],
            ['GET', '/api/rooms/cascade?month_cycle=03-2026'],
        ];
    }
}
