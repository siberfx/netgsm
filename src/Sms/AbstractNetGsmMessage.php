<?php

namespace Siberfx\NetGsm\Sms;

use GuzzleHttp\Exception\GuzzleException;
use Siberfx\NetGsm\Exceptions\AbstractNetGsmException;
use Siberfx\NetGsm\Exceptions\CouldNotSendNotification;
use Siberfx\NetGsm\Exceptions\IncorrectPhoneNumberFormatException;
use Siberfx\NetGsm\Exceptions\NetGsmException;
use Siberfx\NetGsm\NetGsmApiClient;
use Siberfx\NetGsm\NetGsmErrors;

abstract class AbstractNetGsmMessage extends NetGsmApiClient
{
    protected string $url;

    /**
     * NetGsm result code => translation key.
     *
     * @var array<string, string>
     */
    protected array $errorCodes = [];

    /**
     * @var string[]
     */
    protected array $recipients = [];

    protected ?string $header = null;

    protected ?string $message = null;

    protected ?string $code = null;

    protected ?string $jobId = null;

    protected array $response = [];

    public function __construct(?string $message = null, array $defaults = [])
    {
        $this->message = $message;
        $this->defaults = $defaults;
    }

    public static function create(?string $message = null, array $defaults = []): static
    {
        return new static($message, $defaults);
    }

    /**
     * Builds the JSON payload sent to the NetGsm endpoint.
     */
    abstract public function body(): array;

    /**
     * Sets the sms recipients. Accepts an array or a comma separated string.
     *
     * @param  string|int|array<int, string|int>  $recipients
     */
    public function setRecipients(string|int|array $recipients): static
    {
        $recipients = is_array($recipients) ? $recipients : explode(',', (string) $recipients);

        $this->recipients = array_values(array_filter(
            array_map(fn ($recipient) => trim((string) $recipient), $recipients),
            fn (string $recipient) => $recipient !== '',
        ));

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * Sets the sms origin (sender name).
     *
     * @see https://www.netgsm.com.tr/dokuman/#g%C3%B6nderici-ad%C4%B1-sorgulama
     */
    public function setHeader(?string $header): static
    {
        $this->header = $header;

        return $this;
    }

    public function getHeader(): ?string
    {
        return $this->header ?? $this->defaults['header'] ?? null;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getJobId(): ?string
    {
        return $this->jobId;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @throws IncorrectPhoneNumberFormatException
     */
    protected function validateRecipients(): void
    {
        if (count($this->recipients) === 0) {
            throw new IncorrectPhoneNumberFormatException;
        }

        foreach ($this->recipients as $recipient) {
            if (str_contains($recipient, ' ') || strlen($recipient) < 10) {
                throw new IncorrectPhoneNumberFormatException;
            }
        }
    }

    /**
     * Parses the api response and stores the job id.
     *
     * @throws AbstractNetGsmException
     */
    public function parseResponse(array $response): static
    {
        $this->response = $response;

        $this->ensureSuccessful($response, $this->errorCodes, CouldNotSendNotification::class);

        $jobId = $response['jobid'] ?? $response['jobId'] ?? null;

        if ($jobId === null || $jobId === '') {
            throw new NetGsmException(NetGsmErrors::JOB_ID_NOT_FOUND);
        }

        $this->code = (string) $response['code'];
        $this->jobId = (string) $jobId;

        return $this;
    }

    /**
     * Sends the message.
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function send(): static
    {
        $this->validateRecipients();

        return $this->parseResponse($this->callApi('POST', $this->getUrl(), $this->body()));
    }
}
