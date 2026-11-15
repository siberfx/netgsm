<?php

use Illuminate\Support\Carbon;
use Siberfx\NetGsm\Exceptions\NetGsmException;
use Siberfx\NetGsm\Report\NetGsmSmsReport;

function reportJob(string $jobId): array
{
    return [
        'jobid' => $jobId,
        'number' => '905051234567',
        'status' => 1,
        'operator' => 30,
        'msglen' => 1,
        'deliveredDate' => '06.10.2026 14:30:12',
        'errorCode' => 0,
        'referansID' => 'ref-'.$jobId,
    ];
}

it('returns report rows and walks through pages', function () {
    $netgsm = $this->netGsm([
        ['code' => '00', 'jobs' => [reportJob('1'), reportJob('2')]],
        ['code' => '00', 'jobs' => [reportJob('3')]],
    ]);

    $report = (new NetGsmSmsReport)->setPageSize(2);
    $rows = $netgsm->getReports(
        $report,
        Carbon::create(2026, 10, 5, 0, 0, 0),
        Carbon::create(2026, 10, 6, 23, 59, 59),
    );

    expect($rows)->toHaveCount(3);
    expect($rows->first())->toBe([
        'jobId' => '1',
        'phone' => '905051234567',
        'status' => 1,
        'operatorCode' => 30,
        'length' => 1,
        'deliveredDate' => '06.10.2026 14:30:12',
        'errorCode' => 0,
        'referenceId' => 'ref-1',
    ]);

    expect($this->history)->toHaveCount(2);
    expect($this->request()->getUri()->getPath())->toBe('/sms/rest/v2/report');
    expect($this->requestBody())->toBe([
        'startdate' => '05.10.2026 00:00:00',
        'stopdate' => '06.10.2026 23:59:59',
        'pagenumber' => 0,
        'pagesize' => 2,
    ]);
    expect($this->requestBody(1)['pagenumber'])->toBe(1);
});

it('filters by bulk id', function () {
    $netgsm = $this->netGsm([['code' => '00', 'jobs' => [reportJob('99')]]]);

    $rows = $netgsm->getReports(new NetGsmSmsReport, filters: ['bulkId' => [99, 100]]);

    expect($rows->first()['jobId'])->toBe('99');
    expect($this->requestBody()['jobids'])->toBe(['99', '100']);
});

it('fetches a single page when requested', function () {
    $netgsm = $this->netGsm([['code' => '00', 'jobs' => array_fill(0, 100, reportJob('1'))]]);

    $rows = $netgsm->getReports((new NetGsmSmsReport)->setPage(3));

    expect($rows)->toHaveCount(100);
    expect($this->history)->toHaveCount(1);
    expect($this->requestBody()['pagenumber'])->toBe(3);
});

it('returns an empty collection when there are no records', function () {
    $netgsm = $this->netGsm([['code' => '60', 'description' => 'no records']]);

    expect($netgsm->getReports(new NetGsmSmsReport)->isEmpty())->toBeTrue();
});

it('throws on report errors', function () {
    $netgsm = $this->netGsm([['code' => '30']]);

    expect(fn () => $netgsm->getReports(new NetGsmSmsReport))
        ->toThrow(NetGsmException::class, 'Invalid username and password');
});

it('returns job stats', function () {
    $netgsm = $this->netGsm([[
        'code' => '00',
        'stats' => [[
            'status' => 'İletildi',
            'totalMessageLength' => 2,
            'totalSms' => 2,
            'chargeStatement' => 'Ücretlendirildi',
            'statement' => 'Başarılı',
            'domestic' => true,
        ]],
    ]]);

    $stats = $netgsm->getStats('42');

    expect($stats->first()['totalSms'])->toBe(2);
    expect($stats->first()['domestic'])->toBeTrue();
    expect($this->request()->getUri()->getPath())->toBe('/sms/rest/v2/stats');
    expect($this->requestBody())->toBe(['jobid' => '42']);
});
