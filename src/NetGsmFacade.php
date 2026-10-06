<?php

namespace Siberfx\NetGsm;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null sendSms(\Siberfx\NetGsm\Sms\AbstractNetGsmMessage $message)
 * @method static \Illuminate\Support\Collection getReports(\Siberfx\NetGsm\Report\AbstractNetGsmReport $report, ?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null, array $filters = [])
 * @method static \Illuminate\Support\Collection getStats(string $jobId)
 * @method static bool cancel(string $jobId)
 * @method static string[] getHeaders()
 * @method static string|null getCredit()
 * @method static \Illuminate\Support\Collection getAvailablePackages()
 * @method static \Siberfx\NetGsm\Iys\NetGsmIys iys()
 *
 * @see NetGsm
 */
class NetGsmFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return NetGsm::class;
    }
}
