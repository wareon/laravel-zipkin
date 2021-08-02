<?php
/**
 * 把链路跟踪信息先写入REDIS
 * @category   laravel-zipkin
 * @author     wareon  <wareon@qq.com>
 * @license    project
 * @link       http://www.speedtrade.net
 * @ctime:     2021/7/21 10:50
 */


namespace Wareon\Zipkin\Reporter;


use Illuminate\Support\Facades\Redis;
use Zipkin\Recording\Span;
use Zipkin\Reporter;
use RuntimeException;
use Illuminate\Support\Facades\Log;
use Zipkin\Reporters\JsonV2Serializer;

final class  RedisReporter implements Reporter
{
    /**
     * @var string redis键名
     */
    private $redisKey = 'ZIPKIN:LOG';

    public function __construct($redisKey = null)
    {
        if (!is_null($redisKey)) $this->redisKey = $redisKey;
    }

    /**
     * @inheritDoc
     */
    public function report(array $spans): void
    {
        if (\count($spans) === 0) {
            return;
        }

        $serializer = new JsonV2Serializer();
        $payload = $serializer->serialize($spans);
        if ($payload === false) {
            Log::error(
                \sprintf('failed to encode spans with code %d', \json_last_error())
            );
            return;
        }

        try {
            Redis::connection('zipkin')->rpush($this->redisKey, $payload);
        } catch (RuntimeException $e) {
            Log::error(\sprintf('failed to report spans: %s', $e->getMessage()));
        }
    }

    /**
     * @return array|Span[]
     */
    public function flush(): array
    {
        Redis::connection('zipkin')->del($this->redisKey);
        return [];
    }
}
