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
use Ulid\Ulid;

final class UlidTest extends TestCase
{
    public function testGeneratesUppercaseIdentifierByDefault(): void
    {
        $ulid = Ulid::generate();

        $this->assertRegExp('/[0-9][A-Z]/', (string) $ulid);
        $this->assertRegExp('/[0-9][A-Z]/', $ulid->getTime());
        $this->assertRegExp('/[0-9][A-Z]/', $ulid->getRandomness());
        $this->assertFalse($ulid->isLowercase());
    }

    public function testGeneratesLowercaseIdentifierWhenConfigured(): void
    {
        $ulid = Ulid::generate(true);

        $this->assertRegExp('/[0-9][a-z]/', (string) $ulid);
        $this->assertRegExp('/[0-9][a-z]/', $ulid->getTime());
        $this->assertRegExp('/[0-9][a-z]/', $ulid->getRandomness());
        $this->assertTrue($ulid->isLowercase());
    }

    public function testGeneratesTwentySixChars(): void
    {
        $this->assertSame(26, strlen(Ulid::generate()));
        $this->assertSame(26, strlen(Ulid::generateWithTimestamp(1000 * time())));
    }

    public function testGeneratesWithTimestamp(): void
    {
        $ulid = Ulid::generateWithTimestamp($microtimestamp = 531405432123);

        $this->assertSame('00FEX3PS9V', substr((string) $ulid, 0, 10));
        $this->assertSame('00FEX3PS9V', $ulid->getTime());
        $this->assertSame($microtimestamp, $ulid->toTimestamp());
    }

    public function testAddsRandomnessWhenGeneratedMultipleTimes(): void
    {
        $microtimestamp = (int) (microtime(true) * 1000);
        $a = Ulid::generateWithTimestamp($microtimestamp);
        $b = Ulid::generateWithTimestamp($microtimestamp);

        $this->assertEquals($a->getTime(), $b->getTime());
        // Only the last character should be different.
        $this->assertEquals(substr($a, 0, -1), substr($b, 0, -1));
        $this->assertNotEquals($a->getRandomness(), $b->getRandomness());
    }

    public function testGeneratesLexographicallySortableUlids(): void
    {
        $a = Ulid::generate();

        usleep(1000);

        $b = Ulid::generate();

        $ulids = [(string) $b, (string) $a];
        usort($ulids, 'strcmp');

        $this->assertSame([(string) $a, (string) $b], $ulids);
    }

    public function testCreatesFromUppercaseString(): void
    {
        $ulid_default = Ulid::fromString('01AN4Z07BY79KA1307SR9X4MV3');
        $this->assertEquals('01AN4Z07BY79KA1307SR9X4MV3', (string) $ulid_default);
        $this->assertEquals('01AN4Z07BY', $ulid_default->getTime());
        $this->assertEquals('79KA1307SR9X4MV3', $ulid_default->getRandomness());

        $ulid_no_lowercase = Ulid::fromString('01AN4Z07BY79KA1307SR9X4MV3', false);
        $this->assertEquals('01AN4Z07BY79KA1307SR9X4MV3', (string) $ulid_no_lowercase);
        $this->assertEquals('01AN4Z07BY', $ulid_no_lowercase->getTime());
        $this->assertEquals('79KA1307SR9X4MV3', $ulid_no_lowercase->getRandomness());

        $ulid_lowercase = Ulid::fromString('01AN4Z07BY79KA1307SR9X4MV3', true);
        $this->assertEquals('01an4z07by79ka1307sr9x4mv3', (string) $ulid_lowercase);
        $this->assertEquals('01an4z07by', $ulid_lowercase->getTime());
        $this->assertEquals('79ka1307sr9x4mv3', $ulid_lowercase->getRandomness());
    }

    public function testCreatesFromLowercaseString(): void
    {
        $ulid_default = Ulid::fromString('01an4z07by79ka1307sr9x4mv3');
        $this->assertEquals('01AN4Z07BY79KA1307SR9X4MV3', (string) $ulid_default);
        $this->assertEquals('01AN4Z07BY', $ulid_default->getTime());
        $this->assertEquals('79KA1307SR9X4MV3', $ulid_default->getRandomness());

        $ulid_no_lowercase = Ulid::fromString('01an4z07by79ka1307sr9x4mv3', false);
        $this->assertEquals('01AN4Z07BY79KA1307SR9X4MV3', (string) $ulid_no_lowercase);
        $this->assertEquals('01AN4Z07BY', $ulid_no_lowercase->getTime());
        $this->assertEquals('79KA1307SR9X4MV3', $ulid_no_lowercase->getRandomness());

        $ulid_lowercase = Ulid::fromString('01an4z07by79ka1307sr9x4mv3', true);
        $this->assertEquals('01an4z07by79ka1307sr9x4mv3', (string) $ulid_lowercase);
        $this->assertEquals('01an4z07by', $ulid_lowercase->getTime());
        $this->assertEquals('79ka1307sr9x4mv3', $ulid_lowercase->getRandomness());
    }

    /**
     * @expectedException \Ulid\Exception\InvalidUlidStringException
     * @expectedExceptionMessage Invalid ULID string (wrong length):
     */
    public function testCreatesFromStringWithInvalidUlid(): void
    {
        Ulid::fromString('not-a-valid-ulid');
    }

    /**
     * @expectedException \Ulid\Exception\InvalidUlidStringException
     * @expectedExceptionMessage Invalid ULID string (wrong length):
     */
    public function testCreatesFromStringWithTrailingNewLine(): void
    {
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
     * @expectedException \Ulid\Exception\InvalidUlidStringException
     * @expectedExceptionMessage Invalid ULID string (wrong characters):
     */
    public function testCreatesFromStringWithInvalidAlphabet($ulid): void
    {
        Ulid::fromString($ulid);
    }

    public function testConvertsToTimestamp(): void
    {
        $this->assertEquals(531405432123, Ulid::fromString('00FEX3PS9VN3H0TF91703PAT5S')->toTimestamp());
        $this->assertEquals(531405432123, Ulid::fromString('00fex3ps9vn3h0tf91703pat5s', true)->toTimestamp());
    }
}
