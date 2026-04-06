<?php

namespace App\Http\Controllers\Infra;

use App\Http\Controllers\Controller;

class InfraController extends Controller
{
    public function health()
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'laravel-draft',
        ]);
    }
}
