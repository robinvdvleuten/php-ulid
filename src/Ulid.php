<?php

/*
 * This file is part of the ULID package.
 *
 * (c) Robin van der Vleuten <robin@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ulid;

class Ulid
{
    // Crockford's Base32, all lowercased cause it's prettier in URLs.
    const ENCODING_CHARS = '0123456789abcdefghjkmnpqrstvwxyz';
    const ENCODING_LENGTH = 32;

    private static $encoding_chars = self::ENCODING_CHARS;

    /**
     * @var int
     */
    private static $lastGenTime = 0;

    /**
     * @var array
     */
    private static $lastRandChars = [];

    /**
     * @var string
     */
    private $time;

    /**
     * @var string
     */
    private $randomness;

    /**
     * Constructor.
     *
     * @param string $time
     * @param string $randomness
     */
    private function __construct($time, $randomness)
    {
        $this->time = $time;
        $this->randomness = $randomness;
    }

    /**
     * @return Ulid
     */
    public static function fromString($value)
    {
        return new Ulid(substr($value, 0, 10), substr($value, 10));
    }

    /**
     * @return Ulid
     */
    public static function generate()
    {
        $now = intval(microtime(true) * 1000);
        $duplicateTime = $now === static::$lastGenTime;

        $timeChars = '';
        $randChars = '';

        for ($i = 9; $i >= 0; $i--) {
            $mod = $now % static::ENCODING_LENGTH;
            $timeChars = static::$encoding_chars[$mod].$timeChars;
            $now = ($now - $mod) / static::ENCODING_LENGTH;
        }

        if (!$duplicateTime) {
            for ($i = 0; $i < 16; $i++) {
                static::$lastRandChars[$i] = random_int(0, 31);
            }
        } else {
            // If the timestamp hasn't changed since last push,
            // use the same random number, except incremented by 1.
            for ($i = 15; $i >= 0 && static::$lastRandChars[$i] === 31; $i--) {
                static::$lastRandChars[$i] = 0;
            }

            static::$lastRandChars[$i]++;
        }

        for ($i = 0; $i < 16; $i++) {
            $randChars .= static::$encoding_chars[static::$lastRandChars[$i]];
        }

        return new static($timeChars, $randChars);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->time . $this->randomness;
    }
}
