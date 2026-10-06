<?php

namespace Siberfx\NetGsm\Iys\Requests;

class Search
{
    protected string $url = 'iys/search';

    protected ?string $type = null;

    protected ?string $recipient = null;

    protected ?string $recipientType = null;

    protected ?string $refId = null;

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function setRecipient(string $recipient): static
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function setRecipientType(string $recipientType): static
    {
        $this->recipientType = $recipientType;

        return $this;
    }

    public function setRefId(string|int|null $refId): static
    {
        $this->refId = $refId === null ? null : (string) $refId;

        return $this;
    }

    public function getRefId(): ?string
    {
        return $this->refId;
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
            'recipient' => $this->recipient,
            'recipientType' => $this->recipientType,
        ], fn ($value) => $value !== null);
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
