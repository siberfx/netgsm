<?php

namespace Siberfx\NetGsm\Report;

use Illuminate\Support\Collection;
use Siberfx\NetGsm\NetGsmErrors;

/**
 * @see https://www.netgsm.com.tr/dokuman/#rest-v2-rapor
 */
class NetGsmSmsReport extends AbstractNetGsmReport
{
    protected string $url = 'sms/rest/v2/report';

    protected array $errorCodes = [
        '30' => NetGsmErrors::CREDENTIALS_INCORRECT,
        '70' => NetGsmErrors::PARAMETERS_INCORRECT,
        '80' => NetGsmErrors::QUERY_LIMIT_EXCEED,
        '100' => NetGsmErrors::SYSTEM_ERROR,
        '110' => NetGsmErrors::SYSTEM_ERROR,
    ];

    protected array $noResultCodes = ['60'];

    protected function payload(int $page): array
    {
        return array_filter([
            'startdate' => $this->startDate?->format('d.m.Y H:i:s'),
            'stopdate' => $this->endDate?->format('d.m.Y H:i:s'),
            'jobids' => $this->bulkIds ?: null,
            'pagenumber' => $page,
            'pagesize' => $this->pageSize,
        ], fn ($value) => $value !== null);
    }

    protected function parseResponse(array $response): Collection
    {
        return collect($response['jobs'] ?? [])->map(fn (array $job) => [
            'jobId' => isset($job['jobid']) ? (string) $job['jobid'] : null,
            'phone' => isset($job['number']) ? (string) $job['number'] : null,
            'status' => isset($job['status']) ? (int) $job['status'] : null,
            'operatorCode' => isset($job['operator']) ? (int) $job['operator'] : null,
            'length' => isset($job['msglen']) ? (int) $job['msglen'] : null,
            'deliveredDate' => $job['deliveredDate'] ?? null,
            'errorCode' => isset($job['errorCode']) ? (int) $job['errorCode'] : null,
            'referenceId' => $job['referansID'] ?? null,
        ])->values();
    }
}
