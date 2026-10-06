<?php

namespace Siberfx\NetGsm\Report;

use Illuminate\Support\Collection;
use Siberfx\NetGsm\NetGsmErrors;

/**
 * Delivery statistics of a single job (replaces the legacy detail report).
 */
class NetGsmSmsStatsReport extends AbstractNetGsmReport
{
    protected string $url = 'sms/rest/v2/stats';

    protected array $errorCodes = [
        '30' => NetGsmErrors::CREDENTIALS_INCORRECT,
        '70' => NetGsmErrors::PARAMETERS_INCORRECT,
    ];

    protected array $noResultCodes = ['60'];

    protected function payload(int $page): array
    {
        return array_filter([
            'jobid' => $this->bulkIds[0] ?? null,
            'sendDate' => $this->startDate?->format('d.m.Y'),
        ], fn ($value) => $value !== null);
    }

    protected function parseResponse(array $response): Collection
    {
        return collect($response['stats'] ?? [])->map(fn (array $stat) => [
            'status' => $stat['status'] ?? null,
            'totalMessageLength' => isset($stat['totalMessageLength']) ? (int) $stat['totalMessageLength'] : null,
            'totalSms' => isset($stat['totalSms']) ? (int) $stat['totalSms'] : null,
            'chargeStatement' => $stat['chargeStatement'] ?? null,
            'statement' => $stat['statement'] ?? null,
            'domestic' => isset($stat['domestic']) ? (bool) $stat['domestic'] : null,
        ])->values();
    }
}
