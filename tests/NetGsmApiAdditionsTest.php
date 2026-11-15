<?php

use DateTimeImmutable;
use Siberfx\NetGsm\Exceptions\NetGsmException;
use Siberfx\NetGsm\Report\NetGsmSmsStatsReport;

it('sends the stats date with the senddate key', function () {
    $netgsm = $this->netGsm([['code' => '00', 'stats' => []]]);

    $netgsm->getReports(
        (new NetGsmSmsStatsReport)->setBulkId('42'),
        new DateTimeImmutable('2026-11-16'),
    );

    expect($this->requestBody())->toBe(['jobid' => '42', 'senddate' => '16.11.2026']);
});

it('fetches incoming sms for a date range', function () {
    $netgsm = $this->netGsm([['code' => '00', 'messages' => [['no' => '5051234567', 'msg' => 'Merhaba']]]]);

    $response = $netgsm->getInbox(
        new DateTimeImmutable('2025-09-01 00:00'),
        new DateTimeImmutable('2025-09-01 23:00'),
    );

    expect($response['code'])->toBe('00');
    expect($this->request()->getMethod())->toBe('GET');
    expect($this->request()->getUri()->getPath())->toBe('/sms/rest/v2/inbox');

    parse_str($this->request()->getUri()->getQuery(), $query);
    expect($query['startdate'])->toBe('010920250000');
    expect($query['stopdate'])->toBe('010920252300');
});

it('throws for inbox error codes', function () {
    $netgsm = $this->netGsm([['code' => '70', 'description' => 'parameters']]);

    expect(fn () => $netgsm->getInbox(new DateTimeImmutable, new DateTimeImmutable))
        ->toThrow(fn (NetGsmException $exception) => expect($exception->getCode())->toBe(70));
});

it('adds numbers to the blacklist as xml', function () {
    $netgsm = $this->netGsm([['code' => '00']]);

    expect($netgsm->addToBlacklist(['5051234567', '5059876543']))->toBeTrue();

    $request = $this->request();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/sms/blacklist');
    expect($request->getHeaderLine('Content-Type'))->toContain('application/xml');

    $xml = simplexml_load_string((string) $request->getBody());
    expect((string) $xml->header->usercode)->toBe('user');
    expect((string) $xml->header->password)->toBe('secret');
    expect((string) $xml->header->tip)->toBe('2');

    $numbers = [];

    foreach ($xml->body->number as $number) {
        $numbers[] = (string) $number;
    }

    expect($numbers)->toBe(['5051234567', '5059876543']);
});

it('escapes credentials in the blacklist xml', function () {
    $netgsm = $this->netGsm([['code' => '00']], [], ['secret' => 'a&b<c']);

    $netgsm->addToBlacklist('5051234567');

    $xml = simplexml_load_string((string) $this->request()->getBody());
    expect((string) $xml->header->password)->toBe('a&b<c');
});

it('throws for blacklist error codes', function () {
    $netgsm = $this->netGsm([['code' => '30', 'description' => 'credentials']]);

    expect(fn () => $netgsm->addToBlacklist('5051234567'))
        ->toThrow(fn (NetGsmException $exception) => expect($exception->getCode())->toBe(30));
});

it('sets the iys report webhook', function () {
    $netgsm = $this->netGsm([['code' => '0', 'error' => 'false']]);

    $response = $netgsm->iys()->setWebhook('https://example.test/iys');

    expect($response['code'])->toBe('0');
    expect($this->request()->getUri()->getPath())->toBe('/iys/webhook');
    expect($this->requestBody())->toBe([
        'header' => ['username' => 'user', 'password' => 'secret', 'brandCode' => 'brand'],
        'body' => ['webhookUrl' => 'https://example.test/iys'],
    ]);
});
