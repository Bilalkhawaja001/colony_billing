<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class Final19RouteParityClosureTest extends TestCase
{
    /**
     * @dataProvider endpointsProvider
     */
    public function test_final_19_endpoints_are_route_resolvable(string $method, string $uri): void
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
            ['GET', '/'],
            ['GET', '/api/electric-v1/outputs?month_cycle=03-2026'],
            ['POST', '/api/electric-v1/run'],
            ['GET', '/imports/error-report/<token>'],
            ['GET', '/registry/employees/<company_id>'],
            ['GET', '/employees/<company_id>'],
            ['DELETE', '/units/<unit_id>'],
            ['GET', '/api/units/reference'],
            ['GET', '/api/units/reference/<unit_id>'],
            ['GET', '/api/units/reference/cascade'],
            ['POST', '/api/units/reference/upsert'],
            ['GET', '/units/suggest?q=A'],
            ['GET', '/units/resolve/<unit_id>'],
            ['DELETE', '/employees/<company_id>'],
            ['PATCH', '/employees/<company_id>'],
            ['DELETE', '/rooms/1'],
            ['GET', '/meter-reading/latest/<unit_id>'],
            ['DELETE', '/occupancy/1'],
            ['GET', '/billing/print/<month_cycle>/<employee_id>'],
        ];
    }
}
