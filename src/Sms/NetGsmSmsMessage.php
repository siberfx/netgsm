<?php

namespace Siberfx\NetGsm\Sms;

use DateTimeInterface;
use Siberfx\NetGsm\NetGsmErrors;

/**
 * @see https://www.netgsm.com.tr/dokuman/#rest-v2-sms-g%C3%B6nderimi
 */
class NetGsmSmsMessage extends AbstractNetGsmMessage
{
    public const string IYS_FILTER_INFORMATIONAL = '0';

    public const string IYS_FILTER_COMMERCIAL_INDIVIDUAL = '11';

    public const string IYS_FILTER_COMMERCIAL_MERCHANT = '12';

    protected string $url = 'sms/rest/v2/send';

    protected array $errorCodes = [
        '20' => NetGsmErrors::MESSAGE_TOO_LONG,
        '30' => NetGsmErrors::CREDENTIALS_INCORRECT,
        '40' => NetGsmErrors::SENDER_INCORRECT,
        '50' => NetGsmErrors::IYS_CONTROLLED,
        '51' => NetGsmErrors::IYS_BRAND_NOT_FOUND,
        '70' => NetGsmErrors::PARAMETERS_INCORRECT,
        '80' => NetGsmErrors::QUERY_LIMIT_EXCEED,
        '85' => NetGsmErrors::DUPLICATE_LIMIT_EXCEED,
        '100' => NetGsmErrors::SYSTEM_ERROR,
        '101' => NetGsmErrors::SYSTEM_ERROR,
    ];

    protected ?DateTimeInterface $startDate = null;

    protected ?DateTimeInterface $endDate = null;

    protected ?string $encoding = null;

    protected ?string $iysFilter = null;

    protected ?string $partnerCode = null;

    public function setStartDate(?DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function setEndDate(?DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Use "TR" when the message contains Turkish characters.
     */
    public function setEncoding(?string $encoding): static
    {
        $this->encoding = $encoding;

        return $this;
    }

    public function getEncoding(): ?string
    {
        return $this->encoding ?? $this->defaults['encoding'] ?? null;
    }

    /**
     * IYS filter: "0" informational, "11" commercial (individual), "12" commercial (merchant).
     */
    public function setIysFilter(string|int|null $iysFilter): static
    {
        $this->iysFilter = $iysFilter === null ? null : (string) $iysFilter;

        return $this;
    }

    public function getIysFilter(): ?string
    {
        return $this->iysFilter;
    }

    public function setPartnerCode(?string $partnerCode): static
    {
        $this->partnerCode = $partnerCode;

        return $this;
    }

    public function getPartnerCode(): ?string
    {
        return $this->partnerCode ?? $this->defaults['partner_code'] ?? null;
    }

    public function body(): array
    {
        return array_filter([
            'msgheader' => $this->getHeader(),
            'encoding' => $this->getEncoding(),
            'iysfilter' => $this->iysFilter,
            'partnercode' => $this->getPartnerCode(),
            'startdate' => $this->startDate?->format('dmYHi'),
            'stopdate' => $this->endDate?->format('dmYHi'),
            'messages' => array_map(
                fn (string $recipient) => ['msg' => (string) $this->message, 'no' => $recipient],
                $this->recipients,
            ),
        ], fn ($value) => $value !== null && $value !== '');
    }
}
