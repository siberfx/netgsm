<?php

namespace Siberfx\NetGsm;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Siberfx\NetGsm\Exceptions\AbstractNetGsmException;
use Siberfx\NetGsm\Exceptions\NetGsmException;

class NetGsmApiClient
{
    protected ClientInterface $client;

    protected array $credentials = [];

    protected array $defaults = [];

    public function setClient(ClientInterface $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function setCredentials(array $credentials): static
    {
        $this->credentials = $credentials;

        return $this;
    }

    public function setDefaults(array $defaults): static
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * Sends a JSON request to a NetGsm REST v2 endpoint using HTTP Basic authentication.
     *
     * @throws GuzzleException
     * @throws NetGsmException
     */
    protected function callApi(string $method, string $url, array $payload = []): array
    {
        $options = [
            'auth' => [$this->credentials['user_code'] ?? '', $this->credentials['secret'] ?? ''],
            'headers' => [
                'Accept' => 'application/json',
            ],
            'http_errors' => false,
        ];

        if ($appName = $this->defaults['appname'] ?? null) {
            $payload['appname'] ??= $appName;
        }

        if ($method === 'GET') {
            $options['query'] = $payload;
        } else {
            $options['json'] = $payload;
        }

        $response = $this->client->request($method, $url, $options);

        return $this->decode((string) $response->getBody());
    }

    /**
     * @throws NetGsmException
     */
    protected function decode(string $body): array
    {
        $decoded = json_decode($body, true);

        if (! is_array($decoded)) {
            throw new NetGsmException(NetGsmErrors::NETGSM_GENERAL_ERROR);
        }

        return $decoded;
    }

    /**
     * Throws a NetGsmException when the response carries a non-success code.
     *
     * @param  array<string, string>  $errorCodes
     * @param  class-string<AbstractNetGsmException>  $exception
     *
     * @throws AbstractNetGsmException
     */
    protected function ensureSuccessful(
        array $response,
        array $errorCodes,
        string $exception = NetGsmException::class,
    ): void {
        $code = (string) ($response['code'] ?? '');

        if ($code === '00') {
            return;
        }

        throw new $exception($errorCodes[$code] ?? NetGsmErrors::SYSTEM_ERROR, (int) $code);
    }
}
