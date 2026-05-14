<?php

namespace App\Http\Controllers;

use App\Interfaces\ProductSearchInterface;
use App\Services\Decorators\SearchMonitoringDecorator;
use App\Services\SearchWithCacheService;
use App\Services\SearchWithOutCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    protected ProductSearchInterface $searchService;

    public function __construct(ProductSearchInterface $searchService)
    {
        $this->searchService = $searchService;
    }
//-----------------------------------------------------------------------------------------
    public function search(Request $request)
    {
        $keyword = $request->query('q');

        $result = $this->searchService->search($keyword, 5);

        return response()->json([
            'status' => $request->query('use_cache') == 1 ? 'After Caching' : 'Before Caching',
            'result' => $result,
        ]);
    }


       
}
