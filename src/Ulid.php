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
    const ENCODING_CHARS = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
    const ENCODING_LENGTH = 32;

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
     * @var bool
     */
    private $lowercase;

    /**
     * Constructor.
     *
     * @param string $time
     * @param string $randomness
     * @param bool $lowercase
     */
    private function __construct($time, $randomness, $lowercase = false)
    {
        $this->time = $time;
        $this->randomness = $randomness;
        $this->lowercase = $lowercase;
    }

    /**
     * @param string $value
     * @param bool $lowercase
     *
     * @return Ulid
     */
    public static function fromString($value, $lowercase = false)
    {
        return new static(substr($value, 0, 10), substr($value, 10), $lowercase);
    }

    /**
     * @param bool $lowercase
     *
     * @return Ulid
     */
    public static function generate($lowercase = false)
    {
        $now = intval(microtime(true) * 1000);
        $duplicateTime = $now === static::$lastGenTime;

        $timeChars = '';
        $randChars = '';

        $encodingChars = static::ENCODING_CHARS;

        for ($i = 9; $i >= 0; $i--) {
            $mod = $now % static::ENCODING_LENGTH;
            $timeChars = $encodingChars[$mod].$timeChars;
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
            $randChars .= $encodingChars[static::$lastRandChars[$i]];
        }

        return new static($timeChars, $randChars, $lowercase);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return ($value = $this->time . $this->randomness) && $this->lowercase ? strtolower($value) : strtoupper($value);
    }
}
