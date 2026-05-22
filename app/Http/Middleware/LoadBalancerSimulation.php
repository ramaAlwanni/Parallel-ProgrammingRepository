<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LoadBalancerSimulation
{
   public function handle(Request $request, Closure $next)
{
    
    $nodes = [
        ['id' => 'Node_A', 'ip' => '192.168.1.10', 'healthy' => false],
        ['id' => 'Node_B', 'ip' => '192.168.1.11', 'healthy' => false], 
        ['id' => 'Node_C', 'ip' => '192.168.1.12', 'healthy' => false],
    ];

 
    $requestCount = Cache::get('lb_request_counter', 0);
    if ($requestCount > 50) {
        $nodes[] = ['id' => 'Node_D', 'ip' => '192.168.1.13', 'healthy' => false];
    }

   
    $activeNodes = array_values(array_filter($nodes, function($node) {
        return $node['healthy'] === true;
    }));


    if (count($activeNodes) === 0) {
        
        return response()->json([
            'status' => 'Service Unavailable',
            'error_code' => 503,
            'message' => 'جميع خوادم المعالجة مجهدة أو خارج الخدمة حالياً. يرجى إعادة المحاولة بعد قليل.',
            'timestamp' => now()->toIso8601String()
        ], 503); 
    }
  

   
    $counter = Cache::increment('lb_request_counter');
  
    $nodeIndex = $counter % count($activeNodes);
    $assignedNode = $activeNodes[$nodeIndex];

    $response = $next($request);

   
    $response->headers->set('X-Simulated-Node-ID', $assignedNode['id']);
    $response->headers->set('X-Simulated-Node-IP', $assignedNode['ip']);
    $response->headers->set('X-Health-Status', 'Checked');
    $response->headers->set('X-Scaling-Status', count($nodes) > 3 ? 'Scaled-Out' : 'Normal');
    $response->headers->set('X-Active-Nodes-Count', count($activeNodes));

    return $response;
}
}