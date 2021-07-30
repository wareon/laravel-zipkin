<?php
/**
 * Description
 * @category   laravel-zipkin
 * @author     wareon  <wenyongliang@speedtrade.net>
 * @license    project
 * @link       http://www.speedtrade.net
 * @ctime:     2021/7/21 15:50
 */

namespace Wareon\Zipkin\Tests;


use Wareon\Zipkin\Zipkin;

class ZipkinTest extends TestCase
{
    /**
     * @var ZipkinService|null
     */
    public $service = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new Zipkin();
    }

    public function testStart()
    {
        $name = 'parentId test' . date('ymdHis');
        $tags = [
            ['tag' => 'http.status_code', 'val' => '200']
        ];
        $annotate = 'finagle.retry';
        $context = $this->service->spanStart($name, [], $tags);
        $this->service->spanAnnotate($annotate);
        try {
            echo "doSomethingExpensive();";
        } finally {
            $this->service->spanFinish();
        }

        $this->service->tracerFlush();

        $this->assertIsBool(true);
    }

    public function testSpanFinish()
    {

    }

    public function testSpanStart()
    {

    }


    public function testGetTracer()
    {

    }

    public function testGetCallerId()
    {
        $callerId = $this->service->getCallerId();
        echo $callerId;
        $this->assertIsBool(true);
    }

    public function testGetCallerId()
    {
        $callerId = $this->service->getCallerId();
        echo $callerId;
        $this->assertIsBool(true);
    }

    public function testTracerFlush()
    {

    }
}
