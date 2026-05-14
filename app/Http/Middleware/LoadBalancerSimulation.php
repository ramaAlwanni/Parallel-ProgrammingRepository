<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LoadBalancerSimulation
{
    public function handle(Request $request, Closure $next)
    {
        // 1. تعريف الخوادم (أبقيت الأسماء كما هي وأضفت حالة الصحة 'healthy')
        $nodes = [
            ['id' => 'Node_A', 'ip' => '192.168.1.10', 'healthy' => true],
            ['id' => 'Node_B', 'ip' => '192.168.1.11', 'healthy' => false], // محاكاة سيرفر معطل (Health Check)
            ['id' => 'Node_C', 'ip' => '192.168.1.12', 'healthy' => true],
        ];

        // 2. تطبيق الـ Auto-Scaling (التوسع التلقائي)
        // إذا زاد عدد الطلبات عن 50، يتم إضافة Node_D تلقائياً لزيادة السعة
        $requestCount = Cache::get('lb_request_counter', 0);
        if ($requestCount > 50) {
            $nodes[] = ['id' => 'Node_D', 'ip' => '192.168.1.13', 'healthy' => true];
        }

        // 3. تصفية الخوادم بناءً على الـ Health Check
        // استبعاد أي Node غير سليمة (healthy = false)
        $activeNodes = array_values(array_filter($nodes, function($node) {
            return $node['healthy'] === true;
        }));

        // 4. تطبيق خوارزمية Round Robin على الخوادم النشطة فقط
        $counter = Cache::increment('lb_request_counter');
        
        // استخدام count($activeNodes) بدلاً من المصفوفة الثابتة لضمان شمول التوسع
        $nodeIndex = $counter % count($activeNodes);
        $assignedNode = $activeNodes[$nodeIndex];

        $response = $next($request);

        // 5. إضافة المعلومات في الـ Headers لإثبات التحديثات الجديدة
        $response->headers->set('X-Simulated-Node-ID', $assignedNode['id']);
        $response->headers->set('X-Simulated-Node-IP', $assignedNode['ip']);
        $response->headers->set('X-Health-Status', 'Checked');
        $response->headers->set('X-Scaling-Status', count($nodes) > 3 ? 'Scaled-Out' : 'Normal');
        $response->headers->set('X-Active-Nodes-Count', count($activeNodes));

        return $response;
    }
}