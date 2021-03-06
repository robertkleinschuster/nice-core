<?php

declare(strict_types=1);

/**
 * @see       https://github.com/Pars/pars-patterns for the canonical source repository
 * @license   https://github.com/Pars/pars-patterns/blob/master/LICENSE BSD 3-Clause License
 */

namespace Pars\Pattern\Exception;

use Throwable;

class NotFoundException extends CoreException
{
    const FILE_NOT_FOUND_EXCEPTION_CODE = 6100;
    const METHOD_NOT_FOUND_EXCEPTION_CODE = 6200;
    const CLASS_NOT_FOUND_EXCEPTION_CODE = 6300;
    const ATTRIBUTE_NOT_FOUND_EXCEPTION_CODE = 6400;

    public function __construct($message = "", $code = self::NOT_FOUND_EXCEPTION_CODE, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
