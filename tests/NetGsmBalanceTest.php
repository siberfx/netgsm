<?php

use Siberfx\NetGsm\Exceptions\NetGsmException;

it('returns the remaining credit', function () {
    $netgsm = $this->netGsm([['code' => '00', 'balance' => '2,7']]);

    expect($netgsm->getCredit())->toBe('2,7');
    expect($this->request()->getUri()->getPath())->toBe('/balance');
    expect($this->requestBody())->toBe(['usercode' => 'user', 'password' => 'secret', 'stip' => 2]);
});

it('returns the available packages', function () {
    $netgsm = $this->netGsm([[
        'balance' => [
            ['amount' => 1000, 'balance_name' => 'Adet Flash Sms'],
            ['amount' => '953', 'balance_name' => 'Adet OTP Sms'],
        ],
    ]]);

    expect($netgsm->getAvailablePackages()->all())->toBe([
        ['amount' => 1000, 'name' => 'Adet Flash Sms'],
        ['amount' => 953, 'name' => 'Adet OTP Sms'],
    ]);
    expect($this->requestBody()['stip'])->toBe(1);
});

it('throws on balance errors', function () {
    $netgsm = $this->netGsm([['code' => '30', 'description' => 'invalid credentials']]);

    expect(fn () => $netgsm->getCredit())->toThrow(
        NetGsmException::class,
        'Invalid username and password or user does not have API access',
    );
});
