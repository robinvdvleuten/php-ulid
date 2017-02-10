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

class UlidTest extends \PHPUnit_Framework_TestCase
{
    public function testGeneratesTwentySixChars()
    {
        $this->assertSame(26, strlen(Ulid::generate()));
    }

    public function testGeneratesLexographicallySortableUlids()
    {
        $a = Ulid::generate();

        sleep(1);

        $b = Ulid::generate();

        $ulids = [(string) $b, (string) $a];
        usort($ulids, 'strcmp');

        $this->assertSame([(string) $a, (string) $b], $ulids);
    }
}
