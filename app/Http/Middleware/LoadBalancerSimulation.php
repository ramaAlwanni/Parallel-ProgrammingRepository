<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LoadBalancerSimulation
{
    public function handle(Request $request, Closure $next)
    {
        // تعريف 3 خوادم وهمية للمحاكاة
        $nodes = [
            ['id' => 'Node_A', 'ip' => '192.168.1.10'],
            ['id' => 'Node_B', 'ip' => '192.168.1.11'],
            ['id' => 'Node_C', 'ip' => '192.168.1.12'],
        ];
        
        // تطبيق خوارزمية Round Robin باستخدام الكاش لمزامنة الدور
        $counter = Cache::increment('lb_request_counter');
        $nodeIndex = $counter % count($nodes);
        $assignedNode = $nodes[$nodeIndex];

        $response = $next($request);

        // إضافة معلومات التوزيع في الـ Headers لإثبات العملية للمعيد
        $response->headers->set('X-Simulated-Node-ID', $assignedNode['id']);
        $response->headers->set('X-Simulated-Node-IP', $assignedNode['ip']);
        $response->headers->set('X-Balance-Strategy', 'Round-Robin');

        return $response;
    }
}