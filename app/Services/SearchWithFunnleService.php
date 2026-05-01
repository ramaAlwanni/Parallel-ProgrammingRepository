<?php

namespace App\Services;

use App\Interfaces\ProductSearchInterface;
use App\Services\Decorators\SearchMonitoringDecorator;
use Illuminate\Support\Facades\Cache;

class SearchWithFunnleService implements ProductSearchInterface
{
    protected ProductSearchInterface $searchService;

    public function __construct()
    {
        $coreService = new SearchWithOutFunnleService();
        $this->searchService = new SearchMonitoringDecorator($coreService);
    }
//-----------------------------------------------------------------------------------------
    public function search(string $keyword, int $limit = 5)
    {
        $cacheKey = 'search_results_in_cache_' . md5($keyword);

        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult) {
            return [
                'products' => $cachedResult,
                'from_cache' => true
            ];
        }

        $funnelKey = 'search_funnel_' . md5($keyword);

        $products = Cache::funnel($funnelKey)
                            ->limit(1)
                            ->releaseAfter(20)
                            ->block(5)
                            ->then(function () use ($keyword, $cacheKey, $limit) {
                                $result = $this->searchService->search($keyword, $limit);

                                Cache::put($cacheKey, $result, 3600);
                                return [
                                    'products' => $result,
                                    'from_cache' => false
                                ];
                            });
        return $products;
    }
}
