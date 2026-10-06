<?php

namespace Siberfx\NetGsm\Iys;

use GuzzleHttp\Exception\GuzzleException;
use Siberfx\NetGsm\Exceptions\NetGsmException;
use Siberfx\NetGsm\NetGsmApiClient;

abstract class AbstractNetGsmIys extends NetGsmApiClient
{
    protected string $url = '';

    protected string $method = 'POST';

    protected ?string $refId = null;

    protected array $body = [];

    /**
     * Sends the IYS request and returns the decoded response.
     *
     * @throws GuzzleException
     * @throws NetGsmException
     */
    public function send(): array
    {
        $header = array_filter([
            'username' => $this->credentials['user_code'] ?? null,
            'password' => $this->credentials['secret'] ?? null,
            'brandCode' => $this->credentials['brand_code'] ?? null,
            'refid' => $this->refId,
        ], fn ($value) => $value !== null);

        $response = $this->client->request($this->method, $this->url, [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'header' => $header,
                'body' => [
                    'data' => $this->body,
                ],
            ],
            'http_errors' => false,
        ]);

        return $this->decode((string) $response->getBody());
    }
}
