<?php

declare(strict_types=1);

/**
 * @see       https://github.com/Pars/pars-patterns for the canonical source repository
 * @license   https://github.com/Pars/pars-patterns/blob/master/LICENSE BSD 3-Clause License
 */

namespace Pars\Pattern\Exception;

use Throwable;

class LockException extends CoreException
{
    const ATTRIBUTE_LOCK_EXCEPTION_CODE = 3100;


    public function __construct($message = "", $code = self::LOCK_EXCEPTION_CODE, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
