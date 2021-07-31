<?php
/**
 * Zipkin 服务类
 * @category   wms_logging
 * @author     wareon  <wenyongliang@speedtrade.net>
 * @license    project
 * @link       http://www.speedtrade.net
 * @ctime:     2021/7/17 18:02
 */


namespace Wareon\Zipkin;


use Illuminate\Support\Facades\Redis;
use Wareon\Zipkin\Reporter\RedisReporter;
use Zipkin\Endpoint;
use Zipkin\Propagation\TraceContext;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\BinarySampler;
use Zipkin\Span;
use Zipkin\Tracer;
use Zipkin\TracingBuilder;

class Zipkin
{
    public $serviceName = '';
    /**
     * @var Tracer|null
     */
    private $tracer = null;

    /**
     * @var Span|null
     */
    private $span = null;

    /**
     * @var 调用者ID
     */
    private $callerId = 0;

    /**
     * @var 上级缓存时间
     */
    private $parentTimeout = 60;

    /**
     * 最大调用者ID
     * @var int
     */
    private $maxCallerId = 100000000;

    /**
     * @var string redis父级键名前缀
     */
    private $redisParentPrefix = 'ZIPKIN:PARENT:';

    /**
     * @var string redis调用者键名
     */
    private $redisCallerIdKey = 'ZIPKIN:CALLER_ID';

    /**
     * 初始化
     * @param string $serviceName
     */
    public function __construct($serviceName = '')
    {
        $this->serviceName = empty($serviceName) ? config('app.name') : $serviceName;
        // First we create the endpoint that describes our service
        $endpoint = Endpoint::create($this->serviceName);
        $reporter = new RedisReporter();
        $sampler = BinarySampler::createAsAlwaysSample();
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();
        $this->tracer = $tracing->getTracer();
    }

    /**
     * 设置调用者ID
     * @param $callId
     * @author wareon
     */
    public function setCallerId($callId)
    {
        $this->callerId = $callId;
    }

    /**
     * 返回调用者ID
     * @return 调用者ID
     * @author wareon
     */
    public function getCallerId()
    {
        $callId = $this->callerId;
        if(empty($callId)) {
            $callIdKey = config('database.redis.zipkin.caller_id_key', $this->redisCallerIdKey);
            $callId = Redis::connection('zipkin')->get($callIdKey);
        }
        return $callId;
    }

    /**
     * 返回新调用者ID
     * @return mixed
     * @author wareon
     */
    public function newCallerId()
    {
        $callIdKey = config('database.redis.zipkin.caller_id_key', $this->redisCallerIdKey);
        $callId = Redis::connection('zipkin')->incr($callIdKey);
        // 重置调用者ID
        if($callId > $this->maxCallerId) {
            $callId = 1;
            Redis::connection('zipkin')->set($callIdKey, $callId);
        }
        $this->callerId = $callId;
        return $callId;
    }

    /**
     * 设置父级
     * @param $parent
     * @param bool $isNew
     * @author wareon
     */
    public function setParent($parent, $isNew = false)
    {
        $redisParentPrefix = config('database.redis.zipkin.parent_prefix', $this->redisParentPrefix);
        if($isNew) {
            $callId = $this->newCallerId();
        } else {
            $callId = $this->getCallerId();
        }
        $redisParentKey = $redisParentPrefix . $callId;
        $parent = json_encode($parent, JSON_UNESCAPED_UNICODE);
        Redis::connection('zipkin')->set($redisParentKey, $parent);
        Redis::connection('zipkin')->expire($redisParentKey, $this->parentTimeout);
    }

    /**
     * 返回父级
     * @return mixed
     * @author wareon
     */
    public function getParent()
    {
        $redisParentPrefix = config('database.redis.zipkin.parent_prefix', $this->redisParentPrefix);
        $callId = $this->getCallerId();
        $redisParentKey = $redisParentPrefix . $callId;
        $parent = Redis::connection('zipkin')->get($redisParentKey);
        return json_decode($parent, true);
    }

    /**
     * 返回Tracer
     * @return Tracer|null
     * @author wareon
     */
    public function getTracer()
    {
        return $this->tracer;
    }

    /**
     * SPAN启动
     * @param $name
     * @param array $parent
     * @param array $options
     * @return Span|null
     * @author wareon
     */
    public function spanStart($name, $parent = [], $options = [])
    {
        $tracer = $this->getTracer();
        if (!empty($parent)) {
            $context = TraceContext::create(
                $parent['traceId'],
                $parent['spanId'],
                $parent['parentId'],
                $parent['isSampled'],
                $parent['isDebug'],
                $parent['isShared'],
                $parent['usesTraceId128bits']
            );
            $this->span = $tracer->newChild($context);
        } else {
            $this->span = $tracer->newTrace();
        }
        $this->span->setName($name);
        if(isset($options['tags'])){
            $tags = $options['tags'];
            foreach ($tags as $tag) {
                $this->span->tag($tag['tag'], $tag['val']);
            }
        }
        if(isset($options['annotate'])){
            $this->span->annotate($options['annotate']);
        }

        $this->span->start();
        $context = $this->span->getContext();
        if ($context->isEmpty()) {
            return [];
        } else {
            return [
                'traceId' => $context->getTraceId(),
                'spanId' => $context->getSpanId(),
                'parentId' => $context->getParentId(),
                'isSampled' => $context->isSampled(),
                'isDebug' => $context->isDebug(),
                'isShared' => $context->isShared(),
                'usesTraceId128bits' => $context->usesTraceId128bits(),
            ];
        }

    }

    /**
     * SPAN完成
     * @author wareon
     */
    public function spanFinish()
    {
        if (!is_null($this->span))
            $this->span->finish();
    }

    /**
     * tags
     * @param array $tags
     * @author wareon
     */
    public function spanTags(array $tags)
    {
        foreach ($tags as $tag)
            $this->span->tag($tag['tag'], $tag['val']);
    }

    /**
     * SPAN增加注释
     * @param string $value
     * @param int|null $timestamp
     * @author wareon
     */
    public function spanAnnotate(string $value, int $timestamp = null)
    {
        $this->span->annotate($value, $timestamp);
    }

    /**
     * SPAN结束
     * @author wareon
     */
    public function spanEnd()
    {
        $this->spanFinish();
        $this->tracerFlush();
    }

    /**
     * tracer刷新
     * @author wareon
     */
    public function tracerFlush()
    {
        $this->callerId = 0;
        if (!is_null($this->tracer))
            $this->tracer->flush();
    }

}
