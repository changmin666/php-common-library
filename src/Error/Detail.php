<?php
declare (strict_types=1);

namespace PXCommon\Error;

class Detail
{
    private string $reason = '';
    private string $domain = '';
    private array $metadata = [];

    public function __construct(string $reason, string $domain, array $metadata)
    {
        $this->reason = $reason;
        $this->domain = $domain;
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return mixed
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }
}