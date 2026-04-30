<?php

namespace App\Services;

use App\Interfaces\OrderServiceInterface;

class OrderServiceFactory
{
    public function make(bool $optimized): OrderServiceInterface
    {
        $baseService = $optimized
            ? app(SafeOrderService::class)
            : app(UnsafeOrderService::class);

        return new PerformanceMonitorDecorator($baseService, app(OrderBatchReportWriter::class));
    }
}
