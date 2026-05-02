<?php

namespace App\Http\Controllers;

use App\Services\OrderServiceFactory;
use Illuminate\Http\Request;

class OrderController extends Controller
{

public function buy(Request $request, $id) {
    $useOptimization = $request->query('optimized') === 'true';
    
    $baseService = $useOptimization ? new \App\Services\SafeOrderService() : new \App\Services\UnsafeOrderService();

    $monitoredService = new \App\Services\PerformanceMonitorDecorator($baseService);

    $response = $monitoredService->processOrder($id);

        return response()->json([
            'status' => $result,
            'performance' => [
                'mode' => $useOptimization ? 'Optimized' : 'Legacy',
                'execution_time' => $duration . ' seconds',
            ],
        ]);
    }
}