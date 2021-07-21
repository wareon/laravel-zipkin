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
     * @var Span|null
     */
    private $spanChild = null;

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
    public function getTracer() {
        return $this->tracer;
    }

    /**
     * SPAN启动
     * @param $name
     * @param array $tags
     * @return Span|null
     * @author wareon
     */
    public function spanStart($name, $tags = []) {
        $tracer = $this->getTracer();
        $this->span = $tracer->newTrace();
        $this->span->setName($name);
        foreach ($tags as $tag)
            $this->span->tag($tag['tag'], $tag['val']);
        $this->span->start();
        return $this->span;
    }

    /**
     * 增加中间注释
     * @param string $value
     * @param int|null $timestamp
     * @author wareon
     */
    public function spanAnnotate(string $value, int $timestamp = null) {
        $this->span->annotate($value, $timestamp);
    }

    /**
     * SPAN结束
     * @author wareon
     */
    public function spanFinish() {
        if(!is_null($this->span))
        $this->span->finish();
    }

    /**
     * 下级SPAN开始
     * @param $name
     * @param array $tags
     * @return Span|null
     * @author wareon
     */
    public function spanChildStart($name, $tags = []) {
        $tracer = $this->getTracer();
        $this->spanChild = $tracer->newChild($this->span->getContext());
        $this->spanChild->setName($name);
        foreach ($tags as $tag)
            $this->span->tag($tag['tag'], $tag['val']);
        $this->spanChild->start();
        return $this->spanChild;
    }

    /**
     * 下级SPAN中间注释
     * @param string $value
     * @param int|null $timestamp
     * @author wareon
     */
    public function spanChildAnnotate(string $value, int $timestamp = null) {
        $this->spanChild->annotate($value, $timestamp);
    }

    /**
     * 下级SPAN结束
     * @author wareon
     */
    public function spanChildFinish() {
        if(!is_null($this->spanChild))
            $this->spanChild->finish();
    }

    /**
     * tracer刷新
     * @author wareon
     */
    public function tracerFlush() {
        if(!is_null($this->tracer))
            $this->tracer->flush();
    }

}
