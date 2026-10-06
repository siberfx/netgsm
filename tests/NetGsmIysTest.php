<?php

namespace Siberfx\NetGsm\Tests;

use PHPUnit\Framework\Attributes\Test;
use Siberfx\NetGsm\Iys\Requests\Add;
use Siberfx\NetGsm\Iys\Requests\Search;

class NetGsmIysTest extends TestCase
{
    protected function address(): Add
    {
        return (new Add)
            ->setRefId(999999)
            ->setSource('HS_WEB')
            ->setRecipient('+905051234567')
            ->setStatus('ONAY')
            ->setConsentDate('2026-10-06 12:00:00')
            ->setRecipientType('TACIR');
    }

    #[Test]
    public function it_adds_an_address(): void
    {
        $netgsm = $this->netGsm([['code' => '0', 'error' => 'false', 'uid' => 'abc']]);

        $response = $netgsm->iys()->addAddress($this->address()->setType('MESAJ'))->send();

        $this->assertSame('abc', $response['uid']);
        $this->assertSame('/iys/add', $this->request()->getUri()->getPath());
        $this->assertSame([
            'header' => ['username' => 'user', 'password' => 'secret', 'brandCode' => 'brand', 'refid' => '999999'],
            'body' => ['data' => [[
                'type' => 'MESAJ',
                'source' => 'HS_WEB',
                'recipient' => '+905051234567',
                'status' => 'ONAY',
                'consentDate' => '2026-10-06 12:00:00',
                'recipientType' => 'TACIR',
            ]]],
        ], $this->requestBody());
    }

    #[Test]
    public function it_adds_addresses_in_bulk(): void
    {
        $netgsm = $this->netGsm([['code' => '0', 'error' => 'false', 'uid' => 'abc']]);

        $iys = $netgsm->iys();
        $iys->addAddress($this->address()->setType('MESAJ'));
        $iys->addAddress($this->address()->setType('ARAMA'));
        $iys->send();

        $this->assertSame(['MESAJ', 'ARAMA'], array_column($this->requestBody()['body']['data'], 'type'));
    }

    #[Test]
    public function it_searches_an_address(): void
    {
        $netgsm = $this->netGsm([['code' => '0', 'error' => 'false', 'query' => ['status' => 'ONAY']]]);

        $search = (new Search)->setDefaults([
            'type' => 'MESAJ',
            'recipient' => '+905051234567',
            'recipientType' => 'TACIR',
        ]);

        $response = $netgsm->iys()->searchAddress($search)->send();

        $this->assertSame('ONAY', $response['query']['status']);
        $this->assertSame('/iys/search', $this->request()->getUri()->getPath());
        $this->assertSame(
            [['type' => 'MESAJ', 'recipient' => '+905051234567', 'recipientType' => 'TACIR']],
            $this->requestBody()['body']['data'],
        );
        $this->assertArrayNotHasKey('refid', $this->requestBody()['header']);
    }
}
