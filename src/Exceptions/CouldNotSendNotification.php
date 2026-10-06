<?php

namespace Siberfx\NetGsm\Exceptions;

use GuzzleHttp\Exception\RequestException;

class CouldNotSendNotification extends AbstractNetGsmException
{
    /**
     * Thrown when the HTTP layer fails while talking to NetGsm.
     */
    public static function netGsmRespondedWithAnError(RequestException $exception): static
    {
        $response = $exception->getResponse();
        $statusCode = $response?->getStatusCode() ?? 0;
        $description = 'no description given';

        if ($response && ($result = json_decode((string) $response->getBody(), true))) {
            $description = $result['description'] ?? $description;
        }

        return new static("NetGsm responded with an error `{$statusCode} - {$description}`", $statusCode, $exception);
    }
}
