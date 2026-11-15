<?php

namespace Siberfx\NetGsm\Sms;

use DateTimeInterface;
use GuzzleHttp\Exception\GuzzleException;
use Siberfx\NetGsm\Exceptions\AbstractNetGsmException;
use Siberfx\NetGsm\NetGsmApiClient;
use Siberfx\NetGsm\NetGsmErrors;
use SimpleXMLElement;

/**
 * Auxiliary SMS endpoints: cancelling scheduled jobs, listing sender names, incoming SMS and the blacklist.
 */
class NetGsmSmsOperations extends NetGsmApiClient
{
    /**
     * The "tip" value sent with blacklist requests, as in the NetGsm REST v2 examples.
     */
    public const int BLACKLIST_TYPE = 2;

    protected array $errorCodes = [
        '30' => NetGsmErrors::CREDENTIALS_INCORRECT,
        '60' => NetGsmErrors::JOB_ID_NOT_FOUND,
        '70' => NetGsmErrors::PARAMETERS_INCORRECT,
        '100' => NetGsmErrors::SYSTEM_ERROR,
        '101' => NetGsmErrors::SYSTEM_ERROR,
    ];

    /**
     * Cancels a scheduled SMS job.
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function cancel(string $jobId): bool
    {
        $response = $this->callApi('POST', 'sms/rest/v2/cancel', ['jobid' => $jobId]);

        $this->ensureSuccessful($response, $this->errorCodes);

        return true;
    }

    /**
     * Returns the sender names (msgheader) defined on the account.
     *
     * @return string[]
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function getHeaders(): array
    {
        $response = $this->callApi('GET', 'sms/rest/v2/msgheader');

        $this->ensureSuccessful($response, $this->errorCodes);

        return array_values($response['msgheaders'] ?? $response['msgheader'] ?? []);
    }

    /**
     * Returns the incoming SMS received between the given dates (the raw NetGsm response).
     *
     * @return array<string, mixed>
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function getInbox(DateTimeInterface $startDate, DateTimeInterface $endDate): array
    {
        $response = $this->callApi('GET', 'sms/rest/v2/inbox', [
            'startdate' => $startDate->format('dmYHi'),
            'stopdate' => $endDate->format('dmYHi'),
        ]);

        $this->ensureSuccessful($response, $this->errorCodes);

        return $response;
    }

    /**
     * Adds the given phone numbers to the NetGsm SMS blacklist.
     *
     * @param  string[]  $numbers
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function addToBlacklist(array $numbers): bool
    {
        $xml = new SimpleXMLElement('<mainbody/>');

        $header = $xml->addChild('header');
        // addChild() does not escape its value, so XML special characters are escaped here.
        $header->addChild('usercode', $this->escapeXml($this->credentials['user_code'] ?? ''));
        $header->addChild('password', $this->escapeXml($this->credentials['secret'] ?? ''));
        $header->addChild('tip', (string) self::BLACKLIST_TYPE);

        $body = $xml->addChild('body');

        foreach ($numbers as $number) {
            $body->addChild('number', $this->escapeXml($number));
        }

        $response = $this->callXmlApi('sms/blacklist', (string) $xml->asXML());

        $this->ensureSuccessful($response, $this->errorCodes);

        return true;
    }

    protected function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
