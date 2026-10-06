<?php

namespace Siberfx\NetGsm\Tests;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Siberfx\NetGsm\Exceptions\NetGsmException;
use Siberfx\NetGsm\Report\NetGsmSmsReport;

class NetGsmReportTest extends TestCase
{
    protected function job(string $jobId): array
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

    #[Test]
    public function it_returns_report_rows_and_walks_through_pages(): void
    {
        $netgsm = $this->netGsm([
            ['code' => '00', 'jobs' => [$this->job('1'), $this->job('2')]],
            ['code' => '00', 'jobs' => [$this->job('3')]],
        ]);

        $report = (new NetGsmSmsReport)->setPageSize(2);
        $rows = $netgsm->getReports(
            $report,
            Carbon::create(2026, 10, 5, 0, 0, 0),
            Carbon::create(2026, 10, 6, 23, 59, 59),
        );

        $this->assertCount(3, $rows);
        $this->assertSame([
            'jobId' => '1',
            'phone' => '905051234567',
            'status' => 1,
            'operatorCode' => 30,
            'length' => 1,
            'deliveredDate' => '06.10.2026 14:30:12',
            'errorCode' => 0,
            'referenceId' => 'ref-1',
        ], $rows->first());

        $this->assertCount(2, $this->history);
        $this->assertSame('/sms/rest/v2/report', $this->request()->getUri()->getPath());
        $this->assertSame([
            'startdate' => '05.10.2026 00:00:00',
            'stopdate' => '06.10.2026 23:59:59',
            'pagenumber' => 0,
            'pagesize' => 2,
        ], $this->requestBody());
        $this->assertSame(1, $this->requestBody(1)['pagenumber']);
    }

    #[Test]
    public function it_filters_by_bulk_id(): void
    {
        $netgsm = $this->netGsm([['code' => '00', 'jobs' => [$this->job('99')]]]);

        $rows = $netgsm->getReports(new NetGsmSmsReport, filters: ['bulkId' => [99, 100]]);

        $this->assertSame('99', $rows->first()['jobId']);
        $this->assertSame(['99', '100'], $this->requestBody()['jobids']);
    }

    #[Test]
    public function it_fetches_a_single_page_when_requested(): void
    {
        $netgsm = $this->netGsm([['code' => '00', 'jobs' => array_fill(0, 100, $this->job('1'))]]);

        $rows = $netgsm->getReports((new NetGsmSmsReport)->setPage(3));

        $this->assertCount(100, $rows);
        $this->assertCount(1, $this->history);
        $this->assertSame(3, $this->requestBody()['pagenumber']);
    }

    #[Test]
    public function it_returns_an_empty_collection_when_there_are_no_records(): void
    {
        $netgsm = $this->netGsm([['code' => '60', 'description' => 'no records']]);

        $this->assertTrue($netgsm->getReports(new NetGsmSmsReport)->isEmpty());
    }

    #[Test]
    public function it_throws_on_report_errors(): void
    {
        $netgsm = $this->netGsm([['code' => '30']]);

        $this->expectException(NetGsmException::class);
        $this->expectExceptionCode(30);

        $netgsm->getReports(new NetGsmSmsReport);
    }

    #[Test]
    public function it_returns_job_stats(): void
    {
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

        $this->assertSame(2, $stats->first()['totalSms']);
        $this->assertTrue($stats->first()['domestic']);
        $this->assertSame('/sms/rest/v2/stats', $this->request()->getUri()->getPath());
        $this->assertSame(['jobid' => '42'], $this->requestBody());
    }
}
