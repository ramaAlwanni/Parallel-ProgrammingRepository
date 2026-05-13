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
        $lockKey = $Key . '_lock';


        //if result exists in cache
        $cachedResult = Cache::get($Key);
        if ($cachedResult) {
            $result = $cachedResult;
        }

        $result = Cache::lock($lockKey, 10)->block(5, function () use ($Key, $keyword, $limit) {
            //if result doesn't exist in cache
            return Cache::remember(  $Key,
                                now()->addHour(),
                                function () use ($keyword, $limit) {
                                    $service = new SearchWithOutCacheService();
                                    return $service->search($keyword, $limit);
            });
        });
        return [
            'data' => $result,
            'from_cache' => $existsInCache
        ];
    }
}
