<?php

/*
 * This file is part of the ULID package.
 *
 * (c) Robin van der Vleuten <robin@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ulid\Exception;

/**
 * Thrown to indicate that the parsed ULID string is invalid.
 */
class InvalidUlidStringException extends \InvalidArgumentException
{
}
