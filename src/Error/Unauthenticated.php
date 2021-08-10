<?php
declare (strict_types=1);

namespace PXCommon\Error;


class Unauthenticated extends Error
{
    private int $code = 401;
    private string $status = 'UNAUTHORIZED';

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