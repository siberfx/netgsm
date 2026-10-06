# NetGsm for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/siberfx/netgsm.svg?style=flat-square)](https://packagist.org/packages/siberfx/netgsm)
[![Tests](https://img.shields.io/github/actions/workflow/status/siberfx/netgsm/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/siberfx/netgsm/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/siberfx/netgsm.svg?style=flat-square)](https://packagist.org/packages/siberfx/netgsm)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)

Send SMS and OTP messages, query reports and balances, and manage IYS consents through
[NetGsm](https://www.netgsm.com.tr/dokuman/) from Laravel, using NetGsm's REST v2 API.
Includes a notification channel.

| Package | PHP        | Laravel    |
|---------|------------|------------|
| `^5.0`  | `^8.4`     | 12.x, 13.x |

## Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Sending SMS](#sending-sms)
    - [Notification channel](#notification-channel)
    - [Facade](#facade)
    - [Message options](#message-options)
    - [OTP messages](#otp-messages)
    - [Cancelling scheduled messages](#cancelling-scheduled-messages)
    - [Sender names](#sender-names)
- [Reports](#reports)
- [Account balance](#account-balance)
- [IYS integration](#iys-integration)
- [Error handling](#error-handling)
- [Upgrading from 4.x](#upgrading-from-4x)
- [Testing](#testing)
- [Changelog](#changelog)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## Installation

```bash
composer require siberfx/netgsm
```

The service provider and the `NetGsm` facade alias are registered automatically. Publish the config file
if you want to change it:

```bash
php artisan vendor:publish --tag=netgsm-config
```

Translations can be published with `--tag=netgsm-lang`.

## Configuration

Add your NetGsm credentials to `.env`:

```dotenv
NETGSM_USERCODE=8508xxxxxx
NETGSM_SECRET=your-api-password
NETGSM_HEADER=COMPANY        # default sender name (msgheader)
NETGSM_BRANDCODE=            # required for IYS requests only

# Optional
NETGSM_ENCODING=             # set to "TR" when messages contain Turkish characters
NETGSM_APPNAME=              # application name reported to NetGsm
NETGSM_PARTNER_CODE=
NETGSM_BASE_URI=https://api.netgsm.com.tr
NETGSM_TIMEOUT=60
```

`NETGSM_USERCODE` and `NETGSM_SECRET` are your NetGsm API user and password. They are sent with
HTTP Basic authentication, never in the URL.

## Sending SMS

### Notification channel

Tell the notifiable model which number(s) to use:

```php
public function routeNotificationForNetGsm(): string
{
    // "5051234567" or "5051234567, 5441234568"
    return $this->phone;
}
```

Then return a message from `toNetGsm()`:

```php
use Illuminate\Notifications\Notification;
use Siberfx\NetGsm\NetGsmChannel;
use Siberfx\NetGsm\Sms\NetGsmSmsMessage;

class NewUserRegistered extends Notification
{
    public function via(object $notifiable): array
    {
        return [NetGsmChannel::class];
    }

    public function toNetGsm(object $notifiable): NetGsmSmsMessage
    {
        return new NetGsmSmsMessage("Hello! Welcome to the club {$notifiable->name}!");
    }
}
```

Recipients set on the message take precedence over the route:

```php
return (new NetGsmSmsMessage('Your order has shipped!'))->setRecipients(['5051234567', '5441234568']);
```

### Facade

```php
use Siberfx\NetGsm\NetGsmFacade as NetGsm;
use Siberfx\NetGsm\Sms\NetGsmSmsMessage;

$message = (new NetGsmSmsMessage('Your order has shipped!'))
    ->setHeader('COMPANY')
    ->setRecipients(['5051234567', '5441234568']);

$jobId = NetGsm::sendSms($message);
```

`sendSms()` returns the NetGsm job id.

### Message options

```php
use Siberfx\NetGsm\Sms\NetGsmSmsMessage;

(new NetGsmSmsMessage('Great note from the future!'))
    ->setHeader('COMPANY')                       // sender name, defaults to NETGSM_HEADER
    ->setStartDate(now()->addDays(10))           // schedule the message
    ->setEndDate(now()->addDays(11))             // stop trying after this date
    ->setEncoding('TR')                          // Turkish characters
    ->setIysFilter(NetGsmSmsMessage::IYS_FILTER_COMMERCIAL_INDIVIDUAL)
    ->setPartnerCode('PARTNER');
```

IYS filter values:

| Constant                            | Value | Content                         |
|-------------------------------------|-------|---------------------------------|
| `IYS_FILTER_INFORMATIONAL`          | `0`   | Informational                   |
| `IYS_FILTER_COMMERCIAL_INDIVIDUAL`  | `11`  | Commercial, individual recipient |
| `IYS_FILTER_COMMERCIAL_MERCHANT`    | `12`  | Commercial, merchant recipient  |

### OTP messages

Use `NetGsmOtpMessage` to send through NetGsm's OTP endpoint. An OTP message goes to a single recipient,
and scheduling, encoding and IYS options don't apply.

```php
use Siberfx\NetGsm\Sms\NetGsmOtpMessage;

return new NetGsmOtpMessage("Your verification code is {$code}");
```

See the [NetGsm OTP SMS documentation](https://www.netgsm.com.tr/dokuman/#otp-sms).

### Cancelling scheduled messages

```php
NetGsm::cancel($jobId); // true
```

### Sender names

```php
NetGsm::getHeaders(); // ['COMPANY', 'BRAND']
```

## Reports

```php
use Siberfx\NetGsm\Report\NetGsmSmsReport;

$report = (new NetGsmSmsReport)
    ->setStartDate(now()->subDay()->startOfDay())
    ->setEndDate(now()->endOfDay());

$rows = NetGsm::getReports($report);

// or pass dates and filters directly
$rows = NetGsm::getReports(new NetGsmSmsReport, $start, $end, ['bulkId' => [$jobId]]);
```

| Method            | Description                                  | Type                       |
|-------------------|----------------------------------------------|----------------------------|
| `setStartDate()`  | Start date                                   | `DateTimeInterface`        |
| `setEndDate()`    | End date                                     | `DateTimeInterface`        |
| `setBulkId()`     | One or more job ids                          | `string\|int\|array`       |
| `setPageSize()`   | Rows per request (1–100, default 100)        | `int`                      |
| `setPage()`       | Fetch only this page (0 based)               | `?int`                     |

Without `setPage()`, every page is fetched and merged. Each row contains:

| Field           | Description                        |
|-----------------|------------------------------------|
| `jobId`         | NetGsm job id                      |
| `phone`         | Recipient number                   |
| `status`        | Delivery status code               |
| `operatorCode`  | Operator code                      |
| `length`        | Message length (SMS count)         |
| `deliveredDate` | Delivery date                      |
| `errorCode`     | Error code                         |
| `referenceId`   | Reference id                       |

Delivery statistics for a single job:

```php
NetGsm::getStats($jobId);
// [['status' => ..., 'totalMessageLength' => 2, 'totalSms' => 2, 'chargeStatement' => ..., 'statement' => ..., 'domestic' => true]]
```

## Account balance

```php
NetGsm::getCredit(); // "2,7" (TL)

NetGsm::getAvailablePackages();
// Collection: [['amount' => 1000, 'name' => 'Adet Flash Sms'], ['amount' => 953, 'name' => 'Adet OTP Sms']]
```

## IYS integration

IYS requests need `NETGSM_BRANDCODE`.

### Add address

| Method                | Description                       | Type                         | Required |
|-----------------------|-----------------------------------|------------------------------|----------|
| `setRefId()`          | Reference id to query the request | `string\|int`                | No       |
| `setType()`           | Communication type                | `string`                     | Yes      |
| `setSource()`         | Source of permission              | `string`                     | Yes      |
| `setRecipient()`      | Phone number or email address     | `string`                     | Yes      |
| `setStatus()`         | Permission status                 | `string`                     | Yes      |
| `setConsentDate()`    | Permission date                   | `string` (`Y-m-d H:i:s`)     | Yes      |
| `setRecipientType()`  | Recipient type                    | `string`                     | Yes      |
| `setRetailerCode()`   | Retailer code                     | `?int`                       | No       |
| `setRetailerAccess()` | Retailer access                   | `?int`                       | No       |

```php
use Siberfx\NetGsm\Iys\Requests\Add;

$address = (new Add)
    ->setRefId(999999)
    ->setType('MESAJ')
    ->setSource('HS_WEB')
    ->setRecipient('+905XXXXXXXXX')
    ->setStatus('ONAY')
    ->setConsentDate(now()->toDateTimeString())
    ->setRecipientType('TACIR');

$response = NetGsm::iys()->addAddress($address)->send();
// ['code' => '0', 'error' => 'false', 'uid' => '73113cb9-...']
```

Call `addAddress()` several times before `send()` for a bulk insert:

```php
$iys = NetGsm::iys();
$iys->addAddress((clone $address)->setType('MESAJ'));
$iys->addAddress((clone $address)->setType('ARAMA'));
$iys->send();
```

### Search address

```php
use Siberfx\NetGsm\Iys\Requests\Search;

$search = (new Search)
    ->setType('MESAJ')
    ->setRecipient('+905XXXXXXXXX')
    ->setRecipientType('TACIR');

$response = NetGsm::iys()->searchAddress($search)->send();
```

## Error handling

NetGsm result codes are turned into exceptions with translated messages (English and Turkish included).
The NetGsm code is available through `getCode()`.

| Exception                              | When                                               |
|----------------------------------------|----------------------------------------------------|
| `CouldNotSendNotification`             | SMS / OTP sending returned an error code           |
| `IncorrectPhoneNumberFormatException`  | No recipient, or a recipient is invalid            |
| `NetGsmException`                      | Report, balance, cancel or other API errors        |

All of them extend `Siberfx\NetGsm\Exceptions\AbstractNetGsmException`.

## Upgrading from 4.x

Version 5 uses NetGsm's REST v2 API and requires PHP 8.4+ and Laravel 12 or 13.

- Remove `NETGSM_SMS_SENDING_METHOD` and calls to `setSendMethod()`; every message is sent as JSON.
- Replace `NETGSM_LANGUAGE` with `NETGSM_ENCODING` (`TR` for Turkish characters).
- Replace `setAuthorizedData(true)` with `setIysFilter(...)`.
- Replace `NetGsmSmsDetailReport` with `NetGsmSmsReport` or `NetGsm::getStats($jobId)`.
- Report rows have new fields (see [Reports](#reports)); `setVersion()`, `setView()` and `setType()` were removed.
- `getAvailablePackages()` returns `amount` and `name` per package.
- IYS `send()` returns an array instead of a JSON string.
- Republish the config file if you published it before.

## Testing

```bash
composer test
composer lint:test
```

## Changelog

See [CHANGELOG](CHANGELOG.md).

## Security

If you discover a security issue, please email [info@siberfx.com](mailto:info@siberfx.com) instead of
using the issue tracker.

## Credits

- [Selim Görmüş](https://github.com/siberfx)

## License

The MIT License (MIT). See [LICENSE](LICENSE.md) for details.
