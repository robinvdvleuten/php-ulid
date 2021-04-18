<?php

/*
 * This file is part of the ULID package.
 *
 * (c) Robin van der Vleuten <robin@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ulid\Tests;

use Ulid\Ulid;
use PHPUnit\Framework\TestCase;
use Ulid\Exception\InvalidUlidStringException;

/**
 * @group time-sensitive
 */
final class UlidTest extends TestCase
{
    const VALID_MILLISECONDS = 1618715546965;
    const INVALID_MILLISECONDS = 1000000000000000;
    const VALID_UPPER_ULID = '01F3HFE5ANK3KSG1BKVE66AEYM';
    const VALID_LOWER_ULID = '01f3hfe5ank3ksg1bkve66aeym';

    protected function setup(): void
    {
        $this->ulidFromGenerate = Ulid::generate();
        $this->ulidFromTimestamp = Ulid::fromTimestamp(
            self::VALID_MILLISECONDS
        );
    }

    public function testGeneratesUppercaseIdentifierByDefault(): void
    {
        $this->assertRegExp('/[0-9][A-Z]/', (string) $this->ulidFromGenerate);
        $this->assertFalse($this->ulidFromGenerate->isLowercase());
    }

    public function testGeneratesLowercaseIdentifierWhenConfigured(): void
    {
        $ulid = Ulid::generate(true);

        $this->assertRegExp('/[0-9][a-z]/', (string) $ulid);
        $this->assertTrue($ulid->isLowercase());
    }

    public function testGeneratesTwentySixChars(): void
    {
        $this->assertSame(26, strlen($this->ulidFromGenerate));
    }

    public function testAddsRandomnessWhenGeneratedMultipleTimes(): void
    {
        $a = Ulid::generate();
        $b = Ulid::generate();

        $this->assertSame($a->toTimestamp(), $b->toTimestamp());
        // Only the last character should be different.
        $this->assertSame(substr($a, 0, -1), substr($b, 0, -1));
        $this->assertNotSame($a->getRandomness(), $b->getRandomness());
    }

    public function testGeneratesLexographicallySortableUlids(): void
    {
        $a = Ulid::generate();

        sleep(1);

        $b = Ulid::generate();

        $ulids = [(string) $b, (string) $a];
        usort($ulids, 'strcmp');

        $this->assertSame([(string) $a, (string) $b], $ulids);
    }

    public function testCreatesFromUppercaseString(): void
    {
        $this->assertSame(
            self::VALID_UPPER_ULID,
            (string) Ulid::fromString(self::VALID_UPPER_ULID)
        );

        $this->assertSame(
            self::VALID_UPPER_ULID,
            (string) Ulid::fromString(self::VALID_UPPER_ULID, false)
        );

        $this->assertSame(
            self::VALID_LOWER_ULID,
            (string) Ulid::fromString(self::VALID_UPPER_ULID, true)
        );

    }

    public function testCreatesFromLowercaseString(): void
    {
        $this->assertSame(
            self::VALID_UPPER_ULID,
            (string) Ulid::fromString(self::VALID_LOWER_ULID)
        );

        $this->assertSame(
            self::VALID_UPPER_ULID,
            (string) Ulid::fromString(self::VALID_LOWER_ULID, false)
        );

        $this->assertSame(
            self::VALID_LOWER_ULID,
            (string) Ulid::fromString(self::VALID_LOWER_ULID, true)
        );

    }

    public function testCreatesFromStringWithInvalidUlid(): void
    {
        $this->expectException(InvalidUlidStringException::class);
        $this->expectExceptionMessage('Invalid ULID string (wrong length):');

        Ulid::fromString('not-a-valid-ulid');
    }

    public function testCreatesFromStringWithTrailingNewLine(): void
    {
        $this->expectException(InvalidUlidStringException::class);
        $this->expectExceptionMessage('Invalid ULID string (wrong length):');

        Ulid::fromString(self::VALID_UPPER_ULID . "\n");
    }

    public function invalidAlphabetDataProvider(): array
    {
        return [
            'with i' => ['0001eh8yaep8cxp4amwchhdbhi', false],
            'with l' => ['0001eh8yaep8cxp4amwchhdbhl', false],
            'with o' => ['0001eh8yaep8cxp4amwchhdbho', false],
            'with u' => ['0001eh8yaep8cxp4amwchhdbhu', false],
            'with I' => ['0001EH8YAEP8CXP4AMWCHHDBHI', true],
            'with L' => ['0001EH8YAEP8CXP4AMWCHHDBHL', true],
            'with O' => ['0001EH8YAEP8CXP4AMWCHHDBHO', true],
            'with U' => ['0001EH8YAEP8CXP4AMWCHHDBHU', true],
        ];
    }

    /**
     * @dataProvider invalidAlphabetDataProvider
     */
    public function testCreatesFromStringWithInvalidAlphabet($ulid, $stringCase): void
    {
        $this->expectException(InvalidUlidStringException::class);
        $this->expectExceptionMessage(
            'Invalid ULID string (wrong characters):'
        );

        Ulid::fromString($ulid, $stringCase);
    }

    public function testConvertsToTimestamp(): void
    {
        $this->assertEquals(
            self::VALID_MILLISECONDS,
            Ulid::fromString(self::VALID_UPPER_ULID)->toTimestamp()
        );

        $this->assertEquals(
            self::VALID_MILLISECONDS,
            Ulid::fromString(self::VALID_LOWER_ULID, true)->toTimestamp()
        );
    }

    public function testCreateFromTimestamp(): void
    {
        $this->assertSame(
            substr(self::VALID_UPPER_ULID, 0, 10),
            substr((string) $this->ulidFromTimestamp, 0, 10)
        );

        $this->assertSame(
            substr((string) $this->ulidFromTimestamp, 0, 10),
            $this->ulidFromTimestamp->getTime()
        );

        $this->assertSame(
            self::VALID_MILLISECONDS,
            $this->ulidFromTimestamp->toTimestamp()
        );
    }

    public function testAddsRandomnessWhenGeneratedMultipleTimesFromSameTimestamp(): void
    {
        $a = Ulid::fromTimestamp(self::VALID_MILLISECONDS);
        $b = Ulid::fromTimestamp(self::VALID_MILLISECONDS);

        $this->assertSame($a->getTime(), $b->getTime());
        // Only the last character should be different.
        $this->assertSame(substr($a, 0, -1), substr($b, 0, -1));
        $this->assertNotSame($a->getRandomness(), $b->getRandomness());
    }

    public function testCreatesFromTimestampWithInvalidMilliseconds(): void
    {
        $this->expectException(InvalidUlidStringException::class);
        $this->expectExceptionMessage(
            'Invalid ULID string: timestamp too large'
        );

        $ulid = Ulid::fromTimestamp(self::INVALID_MILLISECONDS);
        $ulid->toTimestamp();
    }
}
