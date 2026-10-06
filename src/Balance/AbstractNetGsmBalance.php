<?php

namespace Siberfx\NetGsm\Balance;

use GuzzleHttp\Exception\GuzzleException;
use Siberfx\NetGsm\Exceptions\AbstractNetGsmException;
use Siberfx\NetGsm\NetGsmApiClient;
use Siberfx\NetGsm\NetGsmErrors;

/**
 * @see https://www.netgsm.com.tr/dokuman/#bakiye-sorgulama
 */
abstract class AbstractNetGsmBalance extends NetGsmApiClient
{
    public const int TYPE_PACKAGE = 1;

    public const int TYPE_CREDIT = 2;

    public const int TYPE_ALL = 3;

    protected string $url = 'balance';

    protected array $errorCodes = [
        '30' => NetGsmErrors::CREDENTIALS_INCORRECT,
        '60' => NetGsmErrors::NO_RECORD,
        '70' => NetGsmErrors::PARAMETERS_INCORRECT,
    ];

    /**
     * Queries the balance endpoint with the given "stip" type.
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    protected function query(int $type): array
    {
        $response = $this->callApi('POST', $this->url, [
            'usercode' => $this->credentials['user_code'] ?? '',
            'password' => $this->credentials['secret'] ?? '',
            'stip' => $type,
        ]);

        if (isset($response['code'])) {
            $this->ensureSuccessful($response, $this->errorCodes);
        }

        return $response;
    }
}
