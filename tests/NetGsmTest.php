<?php

use GuzzleHttp\Psr7\Response;
use Siberfx\NetGsm\Exceptions\CouldNotSendNotification;
use Siberfx\NetGsm\Exceptions\IncorrectPhoneNumberFormatException;
use Siberfx\NetGsm\Exceptions\NetGsmException;
use Siberfx\NetGsm\NetGsm;
use Siberfx\NetGsm\NetGsmFacade;
use Siberfx\NetGsm\Sms\NetGsmOtpMessage;
use Siberfx\NetGsm\Sms\NetGsmSmsMessage;

it('sends an sms via rest v2 and returns the job id', function () {
    $netgsm = $this->netGsm([['code' => '00', 'jobid' => '17377215342605050417149344', 'description' => 'queued']]);

    $jobId = $netgsm->sendSms((new NetGsmSmsMessage('Hello'))->setRecipients('5051234567'));

    expect($jobId)->toBe('17377215342605050417149344');

    $request = $this->request();
    expect($request->getMethod())->toBe('POST');
    expect((string) $request->getUri())->toBe('https://api.netgsm.com.tr/sms/rest/v2/send');
    expect($request->getHeaderLine('Authorization'))->toBe('Basic '.base64_encode('user:secret'));
    expect($request->getHeaderLine('Content-Type'))->toContain('application/json');
    expect($this->requestBody())->toBe([
        'msgheader' => 'DEFAULT',
        'messages' => [['msg' => 'Hello', 'no' => '5051234567']],
    ]);
});

it('applies config defaults to the payload', function () {
    $netgsm = $this->netGsm([['code' => '00', 'jobid' => '1']], ['encoding' => 'TR', 'appname' => 'my-app']);

    $netgsm->sendSms((new NetGsmSmsMessage('Şükran'))->setRecipients('5051234567'));

    expect($this->requestBody())
        ->encoding->toBe('TR')
        ->appname->toBe('my-app');
});

it('sends an otp message', function () {
    $netgsm = $this->netGsm([['code' => '00', 'jobId' => '123456', 'description' => 'queued']]);

    $jobId = $netgsm->sendSms((new NetGsmOtpMessage('Code: 1234'))->setRecipients('5051234567'));

    expect($jobId)->toBe('123456');
    expect($this->request()->getUri()->getPath())->toBe('/sms/rest/v2/otp');
    expect($this->requestBody())->toBe(['msgheader' => 'DEFAULT', 'msg' => 'Code: 1234', 'no' => '5051234567']);
});

it('throws a translated exception for netgsm error codes', function () {
    $netgsm = $this->netGsm([$this->jsonResponse(['code' => '40', 'description' => 'invalid header'], 406)]);

    expect(fn () => $netgsm->sendSms((new NetGsmSmsMessage('Hello'))->setRecipients('5051234567')))
        ->toThrow(function (CouldNotSendNotification $exception) {
            expect($exception->getCode())->toBe(40);
            expect($exception->getMessage())->toBe('Message title (sender name) is not defined in system');
        });
});

it('throws when the job id is missing', function () {
    $netgsm = $this->netGsm([['code' => '00']]);

    expect(fn () => $netgsm->sendSms((new NetGsmSmsMessage('Hello'))->setRecipients('5051234567')))
        ->toThrow(NetGsmException::class);
});

it('throws when the response is not json', function () {
    $netgsm = $this->netGsm([new Response(200, [], '30')]);

    expect(fn () => $netgsm->sendSms((new NetGsmSmsMessage('Hello'))->setRecipients('5051234567')))
        ->toThrow(NetGsmException::class);
});

it('validates recipients before sending', function () {
    $netgsm = $this->netGsm([]);

    expect(fn () => $netgsm->sendSms((new NetGsmSmsMessage('Hello'))->setRecipients('12345')))
        ->toThrow(IncorrectPhoneNumberFormatException::class);
});

it('cancels a scheduled job', function () {
    $netgsm = $this->netGsm([['code' => '00', 'jobid' => '42', 'description' => 'cancelled']]);

    expect($netgsm->cancel('42'))->toBeTrue();
    expect($this->request()->getUri()->getPath())->toBe('/sms/rest/v2/cancel');
    expect($this->requestBody())->toBe(['jobid' => '42']);
});

it('lists sender headers', function () {
    $netgsm = $this->netGsm([['code' => '00', 'msgheaders' => ['COMPANY', 'BRAND']]]);

    expect($netgsm->getHeaders())->toBe(['COMPANY', 'BRAND']);
    expect($this->request()->getMethod())->toBe('GET');
    expect($this->request()->getUri()->getPath())->toBe('/sms/rest/v2/msgheader');
});

it('is resolvable from the container and facade', function () {
    $this->app['config']->set('netgsm.credentials.user_code', 'user');

    expect($this->app->make(NetGsm::class))->toBeInstanceOf(NetGsm::class);
    expect(NetGsmFacade::getFacadeRoot())->toBeInstanceOf(NetGsm::class);
});
