<?php
declare (strict_types=1);

namespace PXCommon\Error;

class NotFound extends Error
{
    private int $code = 404;
    private string $status = 'NOT_FOUND';

    public function __construct(string $message, Detail|array $details)
    {
        parent::__construct($message, $details);
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}