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
        if(isset($options['tag'])){
            $tag = $options['tag'];
            foreach ($tags as $tag)
                $this->span->tag($tag['tag'], $tag['val']);
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
        if (!is_null($this->tracer))
            $this->tracer->flush();
    }

}
