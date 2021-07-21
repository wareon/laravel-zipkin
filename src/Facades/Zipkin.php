<?php
/**
 * RakutenRms Facade
 *
 * @author wareon <wareon@qq.com>
 * @date 2020/1/10 12:30
 * @since rakuten rms 1.0
 */

namespace Wareon\Zipkin\Facades;
use Illuminate\Support\Facades\Facade;

/**
 * @method static getTracer()
 * @method static spanStart(string $name, array $tags)
 * @method static spanAnnotate(string $value, int $timestamp = null)
 * @method static spanFinish()
 * @method static spanChildStart(string $name, array $tags)
 * @method static spanChildAnnotate(string $value, int $timestamp = null)
 * @method static spanChildFinish()
 * @method static tracerFlush()
 */

class Zipkin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Zipkin';
    }
}
