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
        return $this->postJson($this->url, ['data' => $this->body], $this->refId);
    }

    /**
     * Posts a JSON body wrapped in the IYS header (username, password, brandCode, refid).
     *
     * @param  array<string, mixed>  $body
     *
     * @throws GuzzleException
     * @throws NetGsmException
     */
    protected function postJson(string $url, array $body, ?string $refId = null): array
    {
        $header = array_filter([
            'username' => $this->credentials['user_code'] ?? null,
            'password' => $this->credentials['secret'] ?? null,
            'brandCode' => $this->credentials['brand_code'] ?? null,
            'refid' => $refId,
        ], fn ($value) => $value !== null);

        $response = $this->client->request($this->method, $url, [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'header' => $header,
                'body' => $body,
            ],
            'http_errors' => false,
        ]);

        return $this->decode((string) $response->getBody());
    }
}
