<?php

namespace Siberfx\NetGsm\Report;

use DateTimeInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Siberfx\NetGsm\Exceptions\AbstractNetGsmException;
use Siberfx\NetGsm\NetGsmApiClient;

abstract class AbstractNetGsmReport extends NetGsmApiClient
{
    protected string $url;

    /**
     * NetGsm result code => translation key.
     *
     * @var array<string, string>
     */
    protected array $errorCodes = [];

    /**
     * Result codes that mean "no (more) records".
     *
     * @var string[]
     */
    protected array $noResultCodes = [];

    protected ?DateTimeInterface $startDate = null;

    protected ?DateTimeInterface $endDate = null;

    /**
     * @var string[]
     */
    protected array $bulkIds = [];

    protected ?int $page = null;

    protected int $pageSize = 100;

    /**
     * Builds the request payload for the given page.
     */
    abstract protected function payload(int $page): array;

    /**
     * Converts a successful api response into report rows.
     */
    abstract protected function parseResponse(array $response): Collection;

    public function setStartDate(DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function setEndDate(DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @param  string|int|array<int, string|int>  $bulkId
     */
    public function setBulkId(string|int|array $bulkId): static
    {
        $this->bulkIds = array_map('strval', is_array($bulkId) ? array_values($bulkId) : explode(',', (string) $bulkId));

        return $this;
    }

    /**
     * Fetches only the given page (0 based) instead of walking every page.
     */
    public function setPage(?int $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function setPageSize(int $pageSize): static
    {
        $this->pageSize = max(1, min(100, $pageSize));

        return $this;
    }

    /**
     * Returns the report rows as a collection, walking through every page unless a page is set.
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function getReports(): Collection
    {
        $page = $this->page ?? 0;
        $results = new Collection;

        do {
            $response = $this->callApi('POST', $this->url, $this->payload($page));

            if (in_array((string) ($response['code'] ?? ''), $this->noResultCodes, true)) {
                break;
            }

            $this->ensureSuccessful($response, $this->errorCodes);

            $rows = $this->parseResponse($response);
            $results = $results->merge($rows);
            $page++;
        } while ($this->page === null && $rows->count() >= $this->pageSize);

        return $results;
    }
}
