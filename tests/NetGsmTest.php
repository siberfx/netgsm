<?php

namespace Siberfx\NetGsm\Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use Siberfx\NetGsm\Exceptions\CouldNotSendNotification;
use Siberfx\NetGsm\Exceptions\IncorrectPhoneNumberFormatException;
use Siberfx\NetGsm\Exceptions\NetGsmException;
use Siberfx\NetGsm\NetGsm;
use Siberfx\NetGsm\NetGsmFacade;
use Siberfx\NetGsm\Sms\NetGsmOtpMessage;
use Siberfx\NetGsm\Sms\NetGsmSmsMessage;

class NetGsmTest extends TestCase
{
    #[Test]
    public function it_sends_an_sms_via_rest_v2_and_returns_the_job_id(): void
    {
        $netgsm = $this->netGsm([['code' => '00', 'jobid' => '17377215342605050417149344', 'description' => 'queued']]);

        $jobId = $netgsm->sendSms((new NetGsmSmsMessage('Hello'))->setRecipients('5051234567'));

        $this->assertSame('17377215342605050417149344', $jobId);

        $request = $this->request();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://api.netgsm.com.tr/sms/rest/v2/send', (string) $request->getUri());
        $this->assertSame('Basic '.base64_encode('user:secret'), $request->getHeaderLine('Authorization'));
        $this->assertStringContainsString('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertSame([
            'msgheader' => 'DEFAULT',
            'messages' => [['msg' => 'Hello', 'no' => '5051234567']],
        ], $this->requestBody());
    }

    #[Test]
    public function it_applies_config_defaults_to_the_payload(): void
    {
        $netgsm = $this->netGsm([['code' => '00', 'jobid' => '1']], ['encoding' => 'TR', 'appname' => 'my-app']);

        $netgsm->sendSms((new NetGsmSmsMessage('Şükran'))->setRecipients('5051234567'));

        $body = $this->requestBody();
        $this->assertSame('TR', $body['encoding']);
        $this->assertSame('my-app', $body['appname']);
    }

    #[Test]
    public function it_sends_an_otp_message(): void
    {
        $netgsm = $this->netGsm([['code' => '00', 'jobId' => '123456', 'description' => 'queued']]);

        $jobId = $netgsm->sendSms((new NetGsmOtpMessage('Code: 1234'))->setRecipients('5051234567'));

        $this->assertSame('123456', $jobId);
        $this->assertSame('/sms/rest/v2/otp', $this->request()->getUri()->getPath());
        $this->assertSame(['msgheader' => 'DEFAULT', 'msg' => 'Code: 1234', 'no' => '5051234567'], $this->requestBody());
    }

    #[Test]
    public function it_throws_a_translated_exception_for_netgsm_error_codes(): void
    {
        $netgsm = $this->netGsm([$this->jsonResponse(['code' => '40', 'description' => 'invalid header'], 406)]);

        try {
            $netgsm->sendSms((new NetGsmSmsMessage('Hello'))->setRecipients('5051234567'));
            $this->fail('Exception was not thrown.');
        } catch (CouldNotSendNotification $exception) {
            $this->assertSame(40, $exception->getCode());
            $this->assertSame('Message title (sender name) is not defined in system', $exception->getMessage());
        }
    }

    #[Test]
    public function it_throws_when_the_job_id_is_missing(): void
    {
        $netgsm = $this->netGsm([['code' => '00']]);

        $this->expectException(NetGsmException::class);

        $netgsm->sendSms((new NetGsmSmsMessage('Hello'))->setRecipients('5051234567'));
    }

    #[Test]
    public function it_throws_when_the_response_is_not_json(): void
    {
        $netgsm = $this->netGsm([new Response(200, [], '30')]);

        $this->expectException(NetGsmException::class);

        $netgsm->sendSms((new NetGsmSmsMessage('Hello'))->setRecipients('5051234567'));
    }

    #[Test]
    public function it_validates_recipients_before_sending(): void
    {
        $netgsm = $this->netGsm([]);

        $this->expectException(IncorrectPhoneNumberFormatException::class);

        $netgsm->sendSms((new NetGsmSmsMessage('Hello'))->setRecipients('12345'));
    }

    #[Test]
    public function it_cancels_a_scheduled_job(): void
    {
        $netgsm = $this->netGsm([['code' => '00', 'jobid' => '42', 'description' => 'cancelled']]);

        $this->assertTrue($netgsm->cancel('42'));
        $this->assertSame('/sms/rest/v2/cancel', $this->request()->getUri()->getPath());
        $this->assertSame(['jobid' => '42'], $this->requestBody());
    }

    #[Test]
    public function it_lists_sender_headers(): void
    {
        $netgsm = $this->netGsm([['code' => '00', 'msgheaders' => ['COMPANY', 'BRAND']]]);

        $this->assertSame(['COMPANY', 'BRAND'], $netgsm->getHeaders());
        $this->assertSame('GET', $this->request()->getMethod());
        $this->assertSame('/sms/rest/v2/msgheader', $this->request()->getUri()->getPath());
    }

    #[Test]
    public function it_is_resolvable_from_the_container_and_facade(): void
    {
        $this->app['config']->set('netgsm.credentials.user_code', 'user');

        $this->assertInstanceOf(NetGsm::class, $this->app->make(NetGsm::class));
        $this->assertInstanceOf(NetGsm::class, NetGsmFacade::getFacadeRoot());
    }
}
