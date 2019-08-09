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

use Ulid\Exception\InvalidUlidStringException;

class Ulid
{
    public const ENCODING_CHARS = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
    public const ENCODING_LENGTH = 32;

    public const TIME_MAX = 281474976710655;
    public const TIME_LENGTH = 10;

    public const RANDOM_LENGTH = 16;

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

    private function __construct(string $time, string $randomness, bool $lowercase = false)
    {
        $this->time = $time;
        $this->randomness = $randomness;
        $this->lowercase = $lowercase;
    }

    public static function fromString(string $value, bool $lowercase = false): self
    {
        if (strlen($value) !== static::TIME_LENGTH + static::RANDOM_LENGTH) {
            throw new InvalidUlidStringException('Invalid ULID string: ' . $value);
        }

        return new static(substr($value, 0, static::TIME_LENGTH), substr($value, static::TIME_LENGTH, static::RANDOM_LENGTH), $lowercase);
    }

    public static function generate(bool $lowercase = false): self
    {
        $now = (int) (microtime(true) * 1000);
        $duplicateTime = $now === static::$lastGenTime;

        static::$lastGenTime = $now;

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

    public function getTime(): string
    {
        return $this->time;
    }

    public function getRandomness(): string
    {
        return $this->randomness;
    }

    public function isLowercase(): bool
    {
        return $this->lowercase;
    }

    public function toTimestamp(): int
    {
        return $this->decodeTime($this->time);
    }

    public function __toString(): string
    {
        return ($value = $this->time . $this->randomness) && $this->lowercase ? strtolower($value) : strtoupper($value);
    }

    private function decodeTime(string $time): int
    {
        $timeChars = str_split(strrev($time));
        $carry = 0;

        foreach ($timeChars as $index => $char) {
            if (($encodingIndex = strripos(static::ENCODING_CHARS, $char)) === false) {
                throw new InvalidUlidStringException('Invalid ULID character: ' . $char);
            }

            $carry += ($encodingIndex * pow(static::ENCODING_LENGTH, $index));
        }

        if ($carry > static::TIME_MAX) {
            throw new InvalidUlidStringException('Invalid ULID string: timestamp too large');
        }

        return $carry;
    }
}
