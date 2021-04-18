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

use PHPUnit\Framework\TestCase;
use Ulid\Exception\InvalidUlidStringException;
use Ulid\Ulid;

/**
 * @group time-sensitive
 */
final class UlidTest extends TestCase
{
    public function testGeneratesUppercaseIdentifierByDefault(): void
    {
        $ulid = Ulid::generate();

        $this->assertRegExp('/[0-9][A-Z]/', (string) $ulid);
        $this->assertFalse($ulid->isLowercase());
    }

    public function testGeneratesLowercaseIdentifierWhenConfigured(): void
    {
        $ulid = Ulid::generate(true);

        $this->assertRegExp('/[0-9][a-z]/', (string) $ulid);
        $this->assertTrue($ulid->isLowercase());
    }

    public function testGeneratesTwentySixChars(): void
    {
        $this->assertSame(26, strlen(Ulid::generate()));
    }

    public function testAddsRandomnessWhenGeneratedMultipleTimes(): void
    {
        $a = Ulid::generate();
        $b = Ulid::generate();

        $this->assertEquals($a->toTimestamp(), $b->toTimestamp());
        // Only the last character should be different.
        $this->assertEquals(substr($a, 0, -1), substr($b, 0, -1));
        $this->assertNotEquals($a->getRandomness(), $b->getRandomness());
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
        $this->assertEquals('01AN4Z07BY79KA1307SR9X4MV3', (string) Ulid::fromString('01AN4Z07BY79KA1307SR9X4MV3'));
        $this->assertEquals('01AN4Z07BY79KA1307SR9X4MV3', (string) Ulid::fromString('01AN4Z07BY79KA1307SR9X4MV3', false));
        $this->assertEquals('01an4z07by79ka1307sr9x4mv3', (string) Ulid::fromString('01AN4Z07BY79KA1307SR9X4MV3', true));
    }

    public function testCreatesFromLowercaseString(): void
    {
        $this->assertEquals('01AN4Z07BY79KA1307SR9X4MV3', (string) Ulid::fromString('01an4z07by79ka1307sr9x4mv3'));
        $this->assertEquals('01AN4Z07BY79KA1307SR9X4MV3', (string) Ulid::fromString('01an4z07by79ka1307sr9x4mv3', false));
        $this->assertEquals('01an4z07by79ka1307sr9x4mv3', (string) Ulid::fromString('01an4z07by79ka1307sr9x4mv3', true));
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

        Ulid::fromString("01AN4Z07BY79KA1307SR9X4MV3\n");
    }

    public function invalidAlphabetDataProvider(): array
    {
        return [
            'with i' => ['0001eh8yaep8cxp4amwchhdbhi'],
            'with l' => ['0001eh8yaep8cxp4amwchhdbhl'],
            'with o' => ['0001eh8yaep8cxp4amwchhdbho'],
            'with u' => ['0001eh8yaep8cxp4amwchhdbhu'],
        ];
    }

    /**
     * @dataProvider invalidAlphabetDataProvider
     */
    public function testCreatesFromStringWithInvalidAlphabet($ulid): void
    {
        $this->expectException(InvalidUlidStringException::class);
        $this->expectExceptionMessage('Invalid ULID string (wrong characters):');

        Ulid::fromString($ulid);
    }

    public function testConvertsToTimestamp(): void
    {
        $this->assertEquals(1561622862, Ulid::fromString('0001EH8YAEP8CXP4AMWCHHDBHJ')->toTimestamp());
        $this->assertEquals(1561622862, Ulid::fromString('0001eh8yaep8cxp4amwchhdbhj', true)->toTimestamp());
    }

    public function testCreateFromTimestamp(): void
    {
        $milliseconds = 1593048767015;
        $ulid = Ulid::fromTimestamp($milliseconds);

        $this->assertSame('01EBMHP6H7', substr((string) $ulid, 0, 10));
        $this->assertSame('01EBMHP6H7', $ulid->getTime());
        $this->assertSame($milliseconds, $ulid->toTimestamp());
    }

    public function testAddsRandomnessWhenGeneratedMultipleTimesByFromTimestamp(): void
    {
        $milliseconds = 1593048767015;
        $a = Ulid::fromTimestamp($milliseconds);
        $b = Ulid::fromTimestamp($milliseconds);

        $this->assertEquals($a->getTime(), $b->getTime());
        // Only the last character should be different.
        $this->assertEquals(substr($a, 0, -1), substr($b, 0, -1));
        $this->assertNotEquals($a->getRandomness(), $b->getRandomness());
    }
    
    public function testCreatesFromTimestampWithInvalidMilliseconds(): void
    {
        $this->expectException(InvalidUlidStringException::class);
        $this->expectExceptionMessage('Invalid ULID string: timestamp too large');

        $ulid = Ulid::fromTimestamp(1000000000000000);
        $ulid->toTimestamp();
    }
}
