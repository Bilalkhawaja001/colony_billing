<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\HouseAllowanceCorrectionRequest;
use App\Services\Billing\HouseAllowanceCorrectionService;

class HouseAllowanceCorrectionController extends Controller
{
    public function __construct(private readonly HouseAllowanceCorrectionService $service)
    {
    }

    public function preview(HouseAllowanceCorrectionRequest $request)
    {
        $result = $this->service->preview($request->validated());
        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);

        return response()->json($result, $code);
    }

    public function apply(HouseAllowanceCorrectionRequest $request)
    {
        $result = $this->service->apply(
            $request->validated(),
            (int) ($request->session()->get('actor_user_id') ?? $request->session()->get('user_id') ?? 0)
        );

        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);

        return response()->json($result, $code);
    }
}
