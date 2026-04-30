<?php

namespace App\Http\Controllers;

use App\Services\Decorators\SearchMonitoringDecorator;
use Illuminate\Http\Request;
use App\Services\SearchWithFunnleService;
use App\Services\SearchWithOutFunnleService;

class ProductController extends Controller
{
    protected $searchWithFunnleService;

    public function __construct(SearchWithFunnleService $searchWithFunnleService)
    {
        $this->searchWithFunnleService = $searchWithFunnleService;
    }
//-----------------------------------------------------------------------------------------
    public function search(Request $request)
    {
        $keyword = $request->query('q');
        $useFunnel = $request->query('use_funnel', '0');

        if ($useFunnel == 0) {
            $coreService = new SearchWithOutFunnleService();
            $tracingService = new SearchMonitoringDecorator($coreService);
            $result = $tracingService->search($keyword, 5);

            return response()->json([
                'status' => 'BEFORE (No Funnel)',
                'trace_id' => $result['trace_id'],
                'products_count' => count($result['products']),
                'execution_time_seconds' => $result['time'],
                'products' => $result['products']
            ]);
        }

        $result = $this->searchWithFunnleService->search($keyword, 5);

        return response()->json([
            'status' => 'AFTER (With Funnel)',
            'trace_id' => $result['trace_id'] ?? null,
            'products_count' => $result['products_count'] ?? 0,
            'execution_time_seconds' => $result['time'] ?? null,
            'products' => $result['products'] ?? []
        ]);
    }

       
}
