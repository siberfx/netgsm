<?php

namespace Siberfx\NetGsm\Sms;

use GuzzleHttp\Exception\GuzzleException;
use Siberfx\NetGsm\Exceptions\AbstractNetGsmException;
use Siberfx\NetGsm\NetGsmApiClient;
use Siberfx\NetGsm\NetGsmErrors;

/**
 * Auxiliary SMS endpoints: cancelling scheduled jobs and listing sender names.
 */
class NetGsmSmsOperations extends NetGsmApiClient
{
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
}
