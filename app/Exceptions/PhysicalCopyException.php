<?php

namespace App\Exceptions;

use RuntimeException;

class PhysicalCopyException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 422,
        public readonly string $errorCode = 'physical_copy_error'
    ) {
        parent::__construct($message);
    }
}
