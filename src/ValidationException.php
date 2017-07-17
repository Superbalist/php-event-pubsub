<?php

namespace Superbalist\EventPubSub;

use Exception;
use Throwable;

class ValidationException extends Exception
{
    /**
     * @var ValidationResult
     */
    protected $validationResult;

    /**
     * @param ValidationResult $validationResult
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        ValidationResult $validationResult,
        $message = 'The event failed validation.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->validationResult = $validationResult;
    }

    /**
     * @return ValidationResult
     */
    public function getValidationResult()
    {
        return $this->validationResult;
    }
}