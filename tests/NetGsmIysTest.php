<?php

use Siberfx\NetGsm\Iys\Requests\Add;
use Siberfx\NetGsm\Iys\Requests\Search;

function iysAddress(): Add
{
    return (new Add)
        ->setRefId(999999)
        ->setSource('HS_WEB')
        ->setRecipient('+905051234567')
        ->setStatus('ONAY')
        ->setConsentDate('2026-10-06 12:00:00')
        ->setRecipientType('TACIR');
}

it('adds an address', function () {
    $netgsm = $this->netGsm([['code' => '0', 'error' => 'false', 'uid' => 'abc']]);

    $response = $netgsm->iys()->addAddress(iysAddress()->setType('MESAJ'))->send();

    expect($response['uid'])->toBe('abc');
    expect($this->request()->getUri()->getPath())->toBe('/iys/add');
    expect($this->requestBody())->toBe([
        'header' => ['username' => 'user', 'password' => 'secret', 'brandCode' => 'brand', 'refid' => '999999'],
        'body' => ['data' => [[
            'type' => 'MESAJ',
            'source' => 'HS_WEB',
            'recipient' => '+905051234567',
            'status' => 'ONAY',
            'consentDate' => '2026-10-06 12:00:00',
            'recipientType' => 'TACIR',
        ]]],
    ]);
});

it('adds addresses in bulk', function () {
    $netgsm = $this->netGsm([['code' => '0', 'error' => 'false', 'uid' => 'abc']]);

    $iys = $netgsm->iys();
    $iys->addAddress(iysAddress()->setType('MESAJ'));
    $iys->addAddress(iysAddress()->setType('ARAMA'));
    $iys->send();

    expect(array_column($this->requestBody()['body']['data'], 'type'))->toBe(['MESAJ', 'ARAMA']);
});

it('searches an address', function () {
    $netgsm = $this->netGsm([['code' => '0', 'error' => 'false', 'query' => ['status' => 'ONAY']]]);

    $search = (new Search)->setDefaults([
        'type' => 'MESAJ',
        'recipient' => '+905051234567',
        'recipientType' => 'TACIR',
    ]);

    $response = $netgsm->iys()->searchAddress($search)->send();

    expect($response['query']['status'])->toBe('ONAY');
    expect($this->request()->getUri()->getPath())->toBe('/iys/search');
    expect($this->requestBody()['body']['data'])->toBe([
        ['type' => 'MESAJ', 'recipient' => '+905051234567', 'recipientType' => 'TACIR'],
    ]);
    expect($this->requestBody()['header'])->not->toHaveKey('refid');
});
