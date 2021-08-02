# laravel-zipkin

# composer require wareon/laravel-zipkin

# .env config

```
# zipkin config
ZIPKIN_ENABLE=true
ZIPKIN_ENDPOINT_URL=http://localhost:9411/api/v2/spans
MICRO_SERVICE_NAME=Laravel zipkin RPC
MICRO_SERVICE_HOST=127.0.0.1
MICRO_SERVICE_PORT=8911
MICRO_SERVICE_TIMEOUT=10000

# zipkin Redis config
ZIPKIN_REDIS_PREFIX=
ZIPKIN_REDIS_KEY=ZIPKIN:LOG
ZIPKIN_REDIS_PARENT_PREFIX=ZIPKIN:PARENT:
ZIPKIN_REDIS_HOST=127.0.0.1
ZIPKIN_REDIS_PASSWORD=12345678
ZIPKIN_REDIS_PORT=6379
ZIPKIN_REDIS_DB=0
```

# public config.php

```
php artisan vendor:publish
```

# change your class

```php
return [
    /* change your class here */
    'processor_class' => \Wareon\Zipkin\Thrift\Demo\DemoProcessor::class,
    'service_class' => \Wareon\Zipkin\Services\Server\DemoService::class,
    'client_class' => \Wareon\Zipkin\Services\Client\DemoServiceClient::class,
];
```
