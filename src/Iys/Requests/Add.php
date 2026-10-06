<?php

namespace Siberfx\NetGsm\Iys\Requests;

class Add
{
    protected string $url = 'iys/add';

    protected ?string $refId = null;

    protected ?string $type = null;

    protected ?string $source = null;

    protected ?string $recipient = null;

    protected ?string $status = null;

    protected ?string $consentDate = null;

    protected ?string $recipientType = null;

    protected ?int $retailerCode = null;

    protected ?int $retailerAccess = null;

    public function setRefId(string|int|null $refId): static
    {
        $this->refId = $refId === null ? null : (string) $refId;

        return $this;
    }

    public function getRefId(): ?string
    {
        return $this->refId;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function setRecipient(string $recipient): static
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function setConsentDate(string $consentDate): static
    {
        $this->consentDate = $consentDate;

        return $this;
    }

    public function setRecipientType(string $recipientType): static
    {
        $this->recipientType = $recipientType;

        return $this;
    }

    public function setRetailerCode(?int $retailerCode): static
    {
        $this->retailerCode = $retailerCode;

        return $this;
    }

    public function setRetailerAccess(?int $retailerAccess): static
    {
        $this->retailerAccess = $retailerAccess;

        return $this;
    }

    public function setDefaults(array $defaults): static
    {
        foreach ($defaults as $key => $value) {
            if (method_exists($this, 'set'.ucfirst($key))) {
                $this->{'set'.ucfirst($key)}($value);
            }
        }

        return $this;
    }

    /**
     * Request body item. The reference id is sent in the request header.
     */
    public function body(): array
    {
        return array_filter([
            'type' => $this->type,
            'source' => $this->source,
            'recipient' => $this->recipient,
            'status' => $this->status,
            'consentDate' => $this->consentDate,
            'recipientType' => $this->recipientType,
            'retailerCode' => $this->retailerCode,
            'retailerAccess' => $this->retailerAccess,
        ], fn ($value) => $value !== null);
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
