<?php

namespace App\Exceptions;

use RuntimeException;

class PhysicalCopyException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 422,
        public readonly string $errorCode = 'physical_copy_error',
        public readonly array $details = []
    ) {
        parent::__construct($message);
    }
}
