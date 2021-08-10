<?php
declare (strict_types=1);

namespace PXCommon\Error;


class Error
{
    private string $message = '';
    private array $details = [];

    public function __construct(string $message, Detail|array $details)
    {
        $this->message = $message;
        if ($details instanceof Detail) {
            $this->details[] = $details;
        } else {
            $this->details = $details;
        }
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return array
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param array $details
     */
    public function setDetails(array $details): void
    {
        $this->details = $details;
    }
}