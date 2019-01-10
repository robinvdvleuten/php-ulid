<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Ulid\Tests;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 * @author Dominic Tubach <dominic.tubach@to.com>
 */
class ClockMock
{
    private static $now;
    public static function withClockMock($enable = null)
    {
        if (null === $enable) {
            return null !== self::$now;
        }
        self::$now = is_numeric($enable) ? (float) $enable : ($enable ? microtime(true) : null);
    }
    public static function time()
    {
        if (null === self::$now) {
            return \time();
        }
        return (int) self::$now;
    }
    public static function sleep($s)
    {
        if (null === self::$now) {
            return \sleep($s);
        }
        self::$now += (int) $s;
        return 0;
    }
    public static function usleep($us)
    {
        if (null === self::$now) {
            return \usleep($us);
        }
        self::$now += $us / 1000000;
    }
    public static function microtime($asFloat = false)
    {
        if (null === self::$now) {
            return \microtime($asFloat);
        }
        if ($asFloat) {
            return self::$now;
        }
        return sprintf('%0.6f00 %d', self::$now - (int) self::$now, (int) self::$now);
    }
    public static function date($format, $timestamp = null)
    {
        if (null === $timestamp) {
            $timestamp = self::time();
        }
        return \date($format, $timestamp);
    }
    public static function register($class)
    {
        $self = \get_called_class();
        $mockedNs = array(substr($class, 0, strrpos($class, '\\')));
        if (0 < strpos($class, '\\Tests\\')) {
            $ns = str_replace('\\Tests\\', '\\', $class);
            $mockedNs[] = substr($ns, 0, strrpos($ns, '\\'));
        } elseif (0 === strpos($class, 'Tests\\')) {
            $mockedNs[] = substr($class, 6, strrpos($class, '\\') - 6);
        }
        foreach ($mockedNs as $ns) {
            if (\function_exists($ns.'\time')) {
                continue;
            }
            eval(<<<EOPHP
namespace $ns;
function time()
{
    return \\$self::time();
}
function microtime(\$asFloat = false)
{
    return \\$self::microtime(\$asFloat);
}
function sleep(\$s)
{
    return \\$self::sleep(\$s);
}
function usleep(\$us)
{
    return \\$self::usleep(\$us);
}
function date(\$format, \$timestamp = null)
{
    return \\$self::date(\$format, \$timestamp);
}
EOPHP
            );
        }
    }
}
