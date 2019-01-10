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
    const FAKE_MICROTIME = 1547129631.2074;

    public function testGeneratesUppercaseIdentiferByDefault(): void
    {
        $this->assertRegExp('/[0-9][A-Z]/', (string) Ulid::generate());
    }

    public function testGeneratesLowercaseIdentiferWhenConfigured(): void
    {
        $this->assertRegExp('/[0-9][a-z]/', (string) Ulid::generate(true));
    }

    public function testGeneratesTwentySixChars(): void
    {
        $this->assertSame(26, \strlen(Ulid::generate()));
    }

    public function testGeneratesLexographicallySortableUlids(): void
    {
        $a = Ulid::generate();

        \sleep(1);

        $b = Ulid::generate();

        $ulids = [(string) $b, (string) $a];
        \usort($ulids, 'strcmp');

        $this->assertSame([(string) $a, (string) $b], $ulids);
    }

    public function testCreatesFromString(): void
    {
        $this->assertEquals('01AN4Z07BY79KA1307SR9X4MV3', (string) Ulid::fromString('01AN4Z07BY79KA1307SR9X4MV3'));
    }

    public function testGeneratesDifferentIdentifiersIfCalledInSameMillisecond(): void
    {
        ClockMock::register('Ulid\Ulid');
        ClockMock::withClockMock(static::FAKE_MICROTIME);

        $this->assertNotEquals(Ulid::generate(), Ulid::generate());
    }
}
