<h1 align="center">NetGsm for Laravel</h1>

<p align="center">
    SMS, OTP, delivery reports, balance and İYS consent management for Laravel,<br>
    built on the <a href="https://www.netgsm.com.tr/dokuman/">NetGsm</a> REST v2 API.
</p>

<p align="center">
    <a href="https://packagist.org/packages/siberfx/netgsm"><img src="https://img.shields.io/packagist/v/siberfx/netgsm.svg?style=flat-square" alt="Latest Version on Packagist"></a>
    <a href="https://github.com/siberfx/netgsm/actions/workflows/tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/siberfx/netgsm/tests.yml?branch=main&label=tests&style=flat-square" alt="Tests"></a>
    <a href="https://packagist.org/packages/siberfx/netgsm"><img src="https://img.shields.io/packagist/php-v/siberfx/netgsm.svg?style=flat-square" alt="PHP Version"></a>
    <a href="https://packagist.org/packages/siberfx/netgsm"><img src="https://img.shields.io/packagist/dt/siberfx/netgsm.svg?style=flat-square" alt="Total Downloads"></a>
    <a href="LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square" alt="License: MIT"></a>
</p>

---

## Features

- **Notification channel**: send SMS from any Laravel notification with `NetGsmChannel`.
- **SMS and OTP**: one recipient or many, a different text per recipient, scheduling, Turkish characters and İYS filters.
- **Job management**: cancel scheduled messages and list your sender names.
- **Reports**: delivery reports with automatic pagination, plus per-job statistics.
- **Balance**: remaining credit (TL) and package balances.
- **İYS**: add and search consent records, single or in bulk.
- **Safe defaults**: credentials go over HTTP Basic auth, and NetGsm error codes come back as translated exceptions (English and Turkish).

## Requirements

| Package | PHP        | Laravel    |
|---------|------------|------------|
| `^5.0`  | 8.4, 8.5   | 12.x, 13.x |

You also need a NetGsm account with an **API user** (created in the NetGsm panel)
and at least one approved sender name (*msgheader*).

## Contents

- [Quick start](#quick-start)
- [Configuration](#configuration)
- [Sending SMS](#sending-sms)
    - [With notifications](#with-notifications)
    - [With the facade](#with-the-facade)
    - [A different text for each recipient](#a-different-text-for-each-recipient)
    - [Scheduling, encoding and İYS](#scheduling-encoding-and-iys)
    - [OTP messages](#otp-messages)
    - [Cancelling and sender names](#cancelling-and-sender-names)
- [Reports](#reports)
- [Account balance](#account-balance)
- [İYS integration](#iys-integration)
- [Error handling](#error-handling)
- [Testing your application](#testing-your-application)
- [API reference](#api-reference)
- [Troubleshooting](#troubleshooting)
- [Upgrading from 4.x](#upgrading-from-4x)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## Quick start

**1. Install the package**

```bash
composer require siberfx/netgsm
```

The service provider and the `NetGsm` facade alias are registered automatically.

**2. Add your credentials to `.env`**

```dotenv
NETGSM_USERCODE=8508xxxxxx
NETGSM_SECRET=your-api-password
NETGSM_HEADER=COMPANY
```

**3. Send a message**

```php
use Siberfx\NetGsm\NetGsmFacade as NetGsm;
use Siberfx\NetGsm\Sms\NetGsmSmsMessage;

$jobId = NetGsm::sendSms(
    (new NetGsmSmsMessage('Hello from Laravel!'))->setRecipients('5051234567')
);
```

## Configuration

All options can be set from `.env`. To change the defaults in code, publish the config file:

```bash
php artisan vendor:publish --tag=netgsm-config   # config/netgsm.php
php artisan vendor:publish --tag=netgsm-lang     # lang/vendor/netgsm
```

| Variable              | Required      | Default                     | Description                                                        |
|-----------------------|---------------|-----------------------------|--------------------------------------------------------------------|
| `NETGSM_USERCODE`     | Yes           | –                           | API username (usually your subscriber number, e.g. `8508xxxxxx`).  |
| `NETGSM_SECRET`       | Yes           | –                           | API password.                                                      |
| `NETGSM_HEADER`       | Yes           | –                           | Default sender name, used when a message doesn't set one.          |
| `NETGSM_BRANDCODE`    | For İYS       | –                           | İYS brand code.                                                    |
| `NETGSM_ENCODING`     | No            | –                           | Set to `TR` if your messages contain Turkish characters.           |
| `NETGSM_APPNAME`      | No            | –                           | Application name reported to NetGsm with each request.             |
| `NETGSM_PARTNER_CODE` | No            | –                           | NetGsm partner code.                                               |
| `NETGSM_BASE_URI`     | No            | `https://api.netgsm.com.tr` | API base URL.                                                      |
| `NETGSM_TIMEOUT`      | No            | `60`                        | HTTP timeout in seconds.                                           |

## Sending SMS

### With notifications

Tell your notifiable model where to send messages:

```php
public function routeNotificationForNetGsm(): string
{
    // One number, or several separated by commas: "5051234567, 5441234568"
    return $this->phone;
}
```

Add `NetGsmChannel` to `via()` and build the message in `toNetGsm()`:

```php
use Illuminate\Notifications\Notification;
use Siberfx\NetGsm\NetGsmChannel;
use Siberfx\NetGsm\Sms\NetGsmSmsMessage;

class OrderShipped extends Notification
{
    public function __construct(private Order $order) {}

    public function via(object $notifiable): array
    {
        return [NetGsmChannel::class];
    }

    public function toNetGsm(object $notifiable): NetGsmSmsMessage
    {
        return new NetGsmSmsMessage("Your order #{$this->order->id} has shipped!");
    }
}
```

```php
$user->notify(new OrderShipped($order));
```

Recipients set on the message take precedence over the model's route:

```php
return (new NetGsmSmsMessage('Your order has shipped!'))->setRecipients(['5051234567', '5441234568']);
```

The channel works with queued notifications (`ShouldQueue`) like any other Laravel channel.

### With the facade

```php
use Siberfx\NetGsm\NetGsmFacade as NetGsm;
use Siberfx\NetGsm\Sms\NetGsmSmsMessage;

$message = (new NetGsmSmsMessage('Your order has shipped!'))
    ->setHeader('COMPANY')
    ->setRecipients(['5051234567', '5441234568']);

$jobId = NetGsm::sendSms($message); // "17377215342605050417149344"
```

Keep the returned job id to query reports or cancel the message later. You can also inject
`Siberfx\NetGsm\NetGsm` instead of using the facade.

### A different text for each recipient

Use `addMessage()` to send personalised texts in a single request. Recipients without their own text get the
default message.

```php
$message = (new NetGsmSmsMessage('Your appointment is tomorrow.'))
    ->setRecipients('5051234567')                        // gets the default text
    ->addMessage('5441234568', 'Ayşe, your appointment is at 10:00.')
    ->addMessage('5321234567', 'Mehmet, your appointment is at 14:30.');

NetGsm::sendSms($message);
```

### Scheduling, encoding and İYS

```php
use Siberfx\NetGsm\Sms\NetGsmSmsMessage;

(new NetGsmSmsMessage('Great note from the future!'))
    ->setHeader('COMPANY')                  // sender name, defaults to NETGSM_HEADER
    ->setStartDate(now()->addDays(10))      // send later
    ->setEndDate(now()->addDays(11))        // stop retrying after this date
    ->setEncoding('TR')                     // Turkish characters (ç, ğ, ı, ö, ş, ü)
    ->setIysFilter(NetGsmSmsMessage::IYS_FILTER_COMMERCIAL_INDIVIDUAL)
    ->setPartnerCode('PARTNER');
```

| İYS filter constant                 | Value | Use for                            |
|-------------------------------------|-------|------------------------------------|
| `IYS_FILTER_INFORMATIONAL`          | `0`   | Informational messages             |
| `IYS_FILTER_COMMERCIAL_INDIVIDUAL`  | `11`  | Commercial messages to individuals |
| `IYS_FILTER_COMMERCIAL_MERCHANT`    | `12`  | Commercial messages to merchants   |

With an İYS filter, NetGsm only delivers to recipients whose consent is recorded in İYS.

### OTP messages

`NetGsmOtpMessage` uses NetGsm's dedicated OTP route, which delivers with priority and needs an OTP package on your
account. An OTP message goes to **one** recipient, and scheduling, encoding and İYS options don't apply.

```php
use Siberfx\NetGsm\Sms\NetGsmOtpMessage;

public function toNetGsm(object $notifiable): NetGsmOtpMessage
{
    return new NetGsmOtpMessage("Your verification code is {$this->code}");
}
```

### Cancelling and sender names

```php
NetGsm::cancel($jobId);   // true. Only scheduled messages that haven't been sent can be cancelled.

NetGsm::getHeaders();     // ['COMPANY', 'BRAND']
```

## Reports

```php
use Siberfx\NetGsm\Report\NetGsmSmsReport;

$report = (new NetGsmSmsReport)
    ->setStartDate(now()->subDay()->startOfDay())
    ->setEndDate(now()->endOfDay());

$rows = NetGsm::getReports($report); // Illuminate\Support\Collection

// Same thing, with dates and filters as arguments:
$rows = NetGsm::getReports(new NetGsmSmsReport, $start, $end, ['bulkId' => [$jobId]]);
```

| Method           | Description                             | Type                   |
|------------------|-----------------------------------------|------------------------|
| `setStartDate()` | Start date                              | `DateTimeInterface`    |
| `setEndDate()`   | End date                                | `DateTimeInterface`    |
| `setBulkId()`    | One or more job ids                     | `string\|int\|array`   |
| `setPageSize()`  | Rows per request (1–100, default 100)   | `int`                  |
| `setPage()`      | Fetch only this page (0 based)          | `?int`                 |

Without `setPage()`, every page is fetched and merged. NetGsm allows about 10 report queries per minute.

Each row looks like this:

```php
[
    'jobId'         => '17377215342605050417149344',
    'phone'         => '905051234567',
    'status'        => 1,
    'operatorCode'  => 30,
    'length'        => 1,
    'deliveredDate' => '06.10.2026 14:30:12',
    'errorCode'     => 0,
    'referenceId'   => '...',
]
```

<details>
<summary><strong>Status codes</strong></summary>

| Code | Meaning                          |
|------|----------------------------------|
| 0    | Pending                          |
| 1    | Delivered                        |
| 2    | Expired (validity period passed) |
| 3    | Invalid or restricted number     |
| 4    | Not sent to operator             |
| 11   | Rejected by operator             |
| 12   | Delivery error                   |
| 13   | Duplicate message                |
| 14   | Insufficient balance             |
| 15   | Blacklisted number               |
| 16   | Rejected by İYS                  |
| 17   | İYS error                        |

</details>

<details>
<summary><strong>Operator codes</strong></summary>

| Code               | Operator           |
|--------------------|--------------------|
| 10                 | Vodafone           |
| 20                 | Türk Telekom       |
| 30                 | Turkcell           |
| 40                 | Netgsm STH         |
| 41                 | Netgsm Mobil       |
| 60                 | Türk Telekom Sabit |
| 70                 | Undefined operator |
| 160                | KKTC Vodafone      |
| 880                | KKTC Turkcell      |
| 212, 213, 214, 215 | International      |

</details>

### Job statistics

```php
NetGsm::getStats($jobId);
// [['status' => ..., 'totalMessageLength' => 2, 'totalSms' => 2,
//   'chargeStatement' => ..., 'statement' => ..., 'domestic' => true]]
```

## Account balance

```php
NetGsm::getCredit();
// "2,7"  (TL, as returned by NetGsm)

NetGsm::getAvailablePackages();
// Collection: [
//     ['amount' => 1000, 'name' => 'Adet Flash Sms'],
//     ['amount' => 953,  'name' => 'Adet OTP Sms'],
// ]
```

## İYS integration

İYS requests need `NETGSM_BRANDCODE`.

### Add a consent

| Method                | Description                       | Type                     | Required |
|-----------------------|-----------------------------------|--------------------------|----------|
| `setRefId()`          | Your reference id for the request | `string\|int`            | No       |
| `setType()`           | `MESAJ`, `ARAMA` or `EPOSTA`      | `string`                 | Yes      |
| `setSource()`         | Consent source, e.g. `HS_WEB`     | `string`                 | Yes      |
| `setRecipient()`      | Phone (`+905…`) or email address  | `string`                 | Yes      |
| `setStatus()`         | `ONAY` or `RET`                   | `string`                 | Yes      |
| `setConsentDate()`    | Consent date                      | `string` (`Y-m-d H:i:s`) | Yes      |
| `setRecipientType()`  | `BIREYSEL` or `TACIR`             | `string`                 | Yes      |
| `setRetailerCode()`   | Retailer code                     | `?int`                   | No       |
| `setRetailerAccess()` | Retailer access                   | `?int`                   | No       |

```php
use Siberfx\NetGsm\Iys\Requests\Add;

$consent = (new Add)
    ->setRefId(999999)
    ->setType('MESAJ')
    ->setSource('HS_WEB')
    ->setRecipient('+905051234567')
    ->setStatus('ONAY')
    ->setConsentDate(now()->toDateTimeString())
    ->setRecipientType('BIREYSEL');

$response = NetGsm::iys()->addAddress($consent)->send();
// ['code' => '0', 'error' => 'false', 'uid' => '73113cb9-...']
```

To add several records in one request, call `addAddress()` more than once before `send()`:

```php
$iys = NetGsm::iys();
$iys->addAddress((clone $consent)->setType('MESAJ'));
$iys->addAddress((clone $consent)->setType('ARAMA'));
$response = $iys->send();
```

Records that fail validation are listed under `erroritem` in the response.

### Search a consent

```php
use Siberfx\NetGsm\Iys\Requests\Search;

$search = (new Search)
    ->setType('MESAJ')
    ->setRecipient('+905051234567')
    ->setRecipientType('BIREYSEL');

$response = NetGsm::iys()->searchAddress($search)->send();
```

## Error handling

NetGsm result codes become exceptions with a translated message. `getCode()` returns the NetGsm code.

```php
use Siberfx\NetGsm\Exceptions\AbstractNetGsmException;
use Siberfx\NetGsm\Exceptions\CouldNotSendNotification;

try {
    NetGsm::sendSms($message);
} catch (CouldNotSendNotification $e) {
    report($e);            // e.g. code 40: "Message title (sender name) is not defined in system"
} catch (AbstractNetGsmException $e) {
    // any other NetGsm error
}
```

| Exception                             | Thrown when                                       |
|---------------------------------------|---------------------------------------------------|
| `CouldNotSendNotification`            | SMS or OTP sending returns an error code          |
| `IncorrectPhoneNumberFormatException` | There's no recipient, or a number is invalid      |
| `NetGsmException`                     | Report, balance, cancel or another API call fails |
| `InvalidConfiguration`                | The package isn't configured                      |

All of them extend `Siberfx\NetGsm\Exceptions\AbstractNetGsmException`.

<details>
<summary><strong>Common NetGsm result codes</strong></summary>

| Code     | Meaning                                                                   |
|----------|---------------------------------------------------------------------------|
| 00       | Success                                                                   |
| 20       | Message text problem, or the message is too long                          |
| 30       | Invalid credentials, no API access, or request from a non-allowed IP      |
| 40 / 41  | Sender name isn't defined or isn't valid                                  |
| 50 / 51  | İYS controlled sending isn't allowed, or no İYS brand found (SMS)          |
| 50–52    | Invalid recipient number (OTP)                                            |
| 60       | No OTP package (OTP), no records (reports), or job id not found (cancel)  |
| 70       | Invalid or missing parameter                                              |
| 80       | Rate limit exceeded                                                       |
| 85       | More than 20 messages to the same number within one minute                |
| 100–110  | NetGsm system error                                                       |

</details>

## Testing your application

Use Laravel's notification fake to check that a notification was sent without calling NetGsm:

```php
use Illuminate\Support\Facades\Notification;
use Siberfx\NetGsm\NetGsmChannel;

Notification::fake();

$user->notify(new OrderShipped($order));

Notification::assertSentTo($user, OrderShipped::class, function ($notification, array $channels) {
    return in_array(NetGsmChannel::class, $channels);
});
```

When you use the facade directly, mock it:

```php
use Siberfx\NetGsm\NetGsmFacade as NetGsm;

NetGsm::shouldReceive('sendSms')->once()->andReturn('123456');
```

## API reference

| Method                                                  | Returns            | Description                         |
|---------------------------------------------------------|--------------------|-------------------------------------|
| `sendSms(AbstractNetGsmMessage $message)`               | `?string`          | Sends an SMS or OTP; returns job id |
| `cancel(string $jobId)`                                 | `bool`             | Cancels a scheduled job             |
| `getHeaders()`                                          | `string[]`         | Sender names on the account         |
| `getReports(AbstractNetGsmReport $report, ...)`         | `Collection`       | Delivery report rows                |
| `getStats(string $jobId)`                               | `Collection`       | Statistics for one job              |
| `getCredit()`                                           | `?string`          | Remaining credit (TL)               |
| `getAvailablePackages()`                                | `Collection`       | Package balances                    |
| `iys()`                                                 | `NetGsmIys`        | İYS request builder                 |

## Troubleshooting

- **Code 30 on every request.** Check that you're using an *API user* (not your panel login) and that API access is
  enabled for it. If the account has an IP restriction, add your server's IP in the NetGsm panel.
- **Code 40.** The sender name must be approved in your NetGsm account. List the approved ones with
  `NetGsm::getHeaders()`.
- **Turkish characters show up wrong.** Set `NETGSM_ENCODING=TR` or call `->setEncoding('TR')`. Turkish encoding
  fits fewer characters into one SMS.
- **Code 85.** NetGsm blocks more than 20 messages to the same number within one minute.
- **The phone number is rejected locally.** Numbers must be at least 10 digits with no spaces, e.g. `5051234567`
  or `905051234567`.

## Upgrading from 4.x

Version 5 uses NetGsm's REST v2 API and requires PHP 8.4+ and Laravel 12 or 13.

| 4.x                                         | 5.x                                                  |
|---------------------------------------------|------------------------------------------------------|
| `NETGSM_SMS_SENDING_METHOD`, `setSendMethod()` | Removed; every message is sent as JSON             |
| `NETGSM_LANGUAGE`                           | `NETGSM_ENCODING` (`TR` for Turkish characters)      |
| `setAuthorizedData(true)`                   | `setIysFilter(...)`                                  |
| `NetGsmSmsDetailReport`                     | `NetGsmSmsReport` or `NetGsm::getStats($jobId)`      |
| `setVersion()`, `setView()`, `setType()`    | Removed; see [Reports](#reports) for the new row format |
| Packages: `amountType`, `packageType`       | `name`                                               |
| İYS `send()` returns a JSON string          | Returns an array                                     |

If you published the config file before, publish it again.

## Contributing

```bash
composer install
composer test        # PHPUnit
composer lint        # fix code style with Laravel Pint
composer lint:test   # check code style
```

Pull requests are welcome. Please add tests and run Pint before you submit.

## Changelog

See [CHANGELOG](CHANGELOG.md) for what changed in each release.

## Security

If you discover a security issue, please email [info@siberfx.com](mailto:info@siberfx.com) instead of using the issue
tracker.

## Credits

- [Selim Görmüş](https://github.com/siberfx)

## License

The MIT License (MIT). See [LICENSE](LICENSE.md) for details.
