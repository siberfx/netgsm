<?php

namespace Siberfx\NetGsm;

use DateTimeInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Collection;
use Siberfx\NetGsm\Balance\NetGsmAvailableCredit;
use Siberfx\NetGsm\Balance\NetGsmPackages;
use Siberfx\NetGsm\Exceptions\AbstractNetGsmException;
use Siberfx\NetGsm\Exceptions\CouldNotSendNotification;
use Siberfx\NetGsm\Iys\NetGsmIys;
use Siberfx\NetGsm\Report\AbstractNetGsmReport;
use Siberfx\NetGsm\Report\NetGsmSmsStatsReport;
use Siberfx\NetGsm\Sms\AbstractNetGsmMessage;
use Siberfx\NetGsm\Sms\NetGsmSmsOperations;

class NetGsm
{
    public function __construct(
        protected ClientInterface $client,
        protected array $credentials = [],
        protected array $defaults = [],
    ) {}

    /**
     * Sends the given SMS / OTP message and returns the NetGsm job id.
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function sendSms(AbstractNetGsmMessage $message): ?string
    {
        try {
            return $this->prepare($message)->send()->getJobId();
        } catch (RequestException $exception) {
            throw CouldNotSendNotification::netGsmRespondedWithAnError($exception);
        }
    }

    /**
     * Returns the sms report rows for the given report object.
     *
     * @param  array<string, mixed>  $filters  extra setters to call on the report, e.g. ['bulkId' => 123]
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function getReports(
        AbstractNetGsmReport $report,
        ?DateTimeInterface $startDate = null,
        ?DateTimeInterface $endDate = null,
        array $filters = [],
    ): Collection {
        $this->prepare($report);

        if ($startDate) {
            $report->setStartDate($startDate);
        }

        if ($endDate) {
            $report->setEndDate($endDate);
        }

        foreach ($filters as $filter => $value) {
            if (method_exists($report, 'set'.ucfirst($filter))) {
                $report->{'set'.ucfirst($filter)}($value);
            }
        }

        return $report->getReports();
    }

    /**
     * Returns delivery statistics of the given job.
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function getStats(string $jobId): Collection
    {
        return $this->getReports((new NetGsmSmsStatsReport)->setBulkId($jobId));
    }

    /**
     * Cancels a scheduled SMS job.
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function cancel(string $jobId): bool
    {
        return $this->prepare(new NetGsmSmsOperations)->cancel($jobId);
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
        return $this->prepare(new NetGsmSmsOperations)->getHeaders();
    }

    /**
     * Returns the remaining credit (TL) on the NetGsm account.
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function getCredit(): ?string
    {
        return $this->prepare(new NetGsmAvailableCredit)->getCredit();
    }

    /**
     * Returns the packages and their remaining amounts on the NetGsm account.
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function getAvailablePackages(): Collection
    {
        return collect($this->prepare(new NetGsmPackages)->getPackages());
    }

    public function iys(): NetGsmIys
    {
        return $this->prepare(new NetGsmIys);
    }

    /**
     * @template T of NetGsmApiClient
     *
     * @param  T  $service
     * @return T
     */
    protected function prepare(NetGsmApiClient $service): NetGsmApiClient
    {
        return $service
            ->setClient($this->client)
            ->setCredentials($this->credentials)
            // Defaults set on the service itself (e.g. via the message constructor) win over the config.
            ->setDefaults(array_merge(
                $this->defaults,
                array_filter($service->getDefaults(), fn ($value) => $value !== null),
            ));
    }
}
