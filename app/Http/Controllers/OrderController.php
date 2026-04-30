<?php

namespace App\Http\Controllers;

use App\Services\OrderServiceFactory;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function buy(Request $request, int $id, OrderServiceFactory $factory)
    {
        $useOptimization = $request->query('optimized') === 'true';
        $service = $factory->make($useOptimization);
        $start = microtime(true);
        $result = $service->processOrder($id);
        $duration = microtime(true) - $start;

        return response()->json([
            'status' => $result,
            'performance' => [
                'mode' => $useOptimization ? 'Optimized' : 'Legacy',
                'execution_time' => $duration . ' seconds',
            ],
        ]);
    }
}
