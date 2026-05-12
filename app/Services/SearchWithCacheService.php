<?php

namespace App\Services;

use App\Interfaces\ProductSearchInterface;
use Illuminate\Support\Facades\Cache;

class SearchWithCacheService implements ProductSearchInterface
{
    public function search(string $keyword, int $limit = 5)
    {
        $Key = 'search_result_' . md5($keyword);
        $existsInCache = Cache::has($Key);

        //if result exists in cache
        $cachedResult = Cache::get($Key);
        if ($cachedResult) {
            $result = $cachedResult;
        }

        //if result doesn't exist in cache
        $result = Cache::remember( $Key,
                                    now()->addHour(),
                                    function () use ($keyword, $limit) {
                                        $service = new SearchWithOutCacheService();
                                        return $service->search($keyword, $limit);
                                    });

        return [
            'data' => $result,
            'from_cache' => $existsInCache
        ];                
           
    }
}
