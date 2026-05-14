<?php

namespace App\Http\Controllers;

use App\Interfaces\OrderServiceInterface;
use App\Jobs\ProcessOrderChunkJob; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class OrderController extends Controller {

public function buy(Request $request, int $id, OrderServiceInterface $orderService) {
    $useOptimization = $request->query('optimized') === 'true';
    
    if ($useOptimization) {
        $service = $orderService;
    } else {
        $service = new \App\Services\PerformanceMonitorDecorator(new \App\Services\UnsafeOrderService());
    }

    $response = $service->processOrder($id);

    return response()->json([
        'status' => $response['result'] ?? 'Success', 
        'trace_id' => $response['trace_id'] ?? null,
        'performance' => [
            'mode' => $useOptimization ? 'Optimized' : 'Legacy',
            'execution_time' => ($response['time'] ?? '0') . ' seconds'
        ]
    ]);
}

    public function runInventoryBatch(Request $request) {
        $optimized = $request->query('optimized') === 'true';
        $productIds = [1, 2, 3]; 

        $batch = Bus::batch([
            new ProcessOrderChunkJob($productIds, $optimized, 1, 1)
        ])->dispatch();

        return response()->json([
            'message' => 'Batch processing started',
            'batch_id' => $batch->id
        ]);
    }
}