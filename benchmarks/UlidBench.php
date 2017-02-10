<?php

/*
 * This file is part of the ULID package.
 *
 * (c) Robin van der Vleuten <robin@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ulid\Benchmark;

use Ulid\Ulid;

class UlidBench
{
    /**
     * @Revs(10000)
     */
    public function benchGenerate()
    {
        Ulid::generate();
    }
}
