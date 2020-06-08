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

        $this->assertEquals($a->getTime(), $b->getTime());
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
        $this->assertEquals(1591616647.074, Ulid::fromString('01EA9VXBX2HJYMFFDXC60RV7RZ')->toTimestamp());
        $this->assertEquals(1591616647.074, Ulid::fromString('01ea9vxbx2hjymffdxc60rv7rz', true)->toTimestamp());
    }
    
    public function testIsFloatToTimestamp(): void
    {
        $this->assertIsFloat( Ulid::fromString('01EA9VXBX2HJYMFFDXC60RV7RZ')->toTimestamp());
        $this->assertIsFloat( Ulid::fromString('01ea9vxbx2hjymffdxc60rv7rz', true)->toTimestamp());
        $this->assertIsFloat( Ulid::generate(true)->toTimestamp());
        $this->assertIsFloat( Ulid::generate()->toTimestamp());
    }
}
