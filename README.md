## High-Performance E-Commerce Backend Engine
## 📝 Project Description 
This project is a robust, high-performance backend system built for e-commerce platforms, specifically designed to handle thousands of concurrent requests while maintaining strict data integrity and system stability. 
Unlike traditional e-commerce applications, the primary focus here is on Parallel Programming concepts and Non-Functional Requirements (NFRs) such as concurrency control, 
resource management, and asynchronous processing.

# 1. تحميل Redis من:
https://github.com/redis-windows/redis-windows/releases

# نزل:
Redis-8.6.2-Windows-x64-msys2-with-Service.zip

# 2. فك الضغط في مجلد (مثلاً C:\redis)

# 3. تشغيل redis-server.exe (اتركه شغال في الخلفية)

# 4. composer install

# .env غير هذا السطر من:
CACHE_STORE=database
# إلى:
CACHE_STORE=redis

# وتأكد من وجود:
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_CLIENT=predis

# تشغيل المشروع 
php artisan serve

## Daily Batch Processing (Chunked)

This project now supports a background daily batch process for order handling and inventory updates.

- Command: `php artisan orders:process-daily --optimized=true --chunk=200`
- Scheduler: runs daily at `01:00` via Laravel scheduler.
- Processing mode:
	- `optimized=true` uses `SafeOrderService` (transaction + row locking)
	- `optimized=false` uses `UnsafeOrderService` (legacy behavior)

- Command: `php artisan queue:work --queue=default`
in a dedicated terminal
## AOP / Decorator Monitoring

`PerformanceMonitorDecorator` wraps order services and logs before/after snapshots around each chunk.

Chunk results are written to a dedicated JSONL report file instead of the noisy main Laravel log.

Report file:

- `storage/app/reports/order-batches/order-batch-YYYY-MM-DD.jsonl`

Laravel log now keeps only small summary entries such as report write confirmations and batch status.

Payload includes:

- `before`: tracked products, stock sum, in-stock count
- `after`: same metrics after processing
- `delta`: stock change
- `duration_seconds`
- `throughput_items_per_second`
- chunk metadata (`chunk_index`, `total_chunks`, `batch_id`)

This allows you to compare runs and clearly show improvement after applying batch processing.
