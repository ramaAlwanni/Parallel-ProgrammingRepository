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
