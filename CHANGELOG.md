# Changelog

All notable changes to `siberfx/netgsm` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [5.0.0] - 2026-10-06

This release moves the package to NetGsm's REST v2 API and modern Laravel. It contains breaking changes,
see [Upgrading from 4.x](README.md#upgrading-from-4x).

### Added

- Laravel 12 and Laravel 13 support.
- PHP 8.4 and PHP 8.5 support.
- `NetGsm::cancel($jobId)` to cancel scheduled SMS jobs (`sms/rest/v2/cancel`).
- `NetGsm::getHeaders()` to list the sender names defined on the account (`sms/rest/v2/msgheader`).
- `NetGsm::getStats($jobId)` and `NetGsmSmsStatsReport` for job delivery statistics (`sms/rest/v2/stats`).
- `NetGsmSmsMessage::setEncoding()`, `setIysFilter()` and `setPartnerCode()`.
- `NETGSM_ENCODING`, `NETGSM_APPNAME` and `NETGSM_PARTNER_CODE` configuration options.
- `NetGsmSmsReport::setPageSize()`; reports are paginated automatically.
- Translations for new NetGsm result codes (IYS and duplicate sending limits).
- Laravel Pint coding standard (`composer lint`, `composer lint:test`).

### Changed

- SMS sending uses `POST sms/rest/v2/send` with HTTP Basic authentication and a JSON body.
- OTP sending uses `POST sms/rest/v2/otp`.
- SMS reports use `POST sms/rest/v2/report` and return `jobId`, `phone`, `status`, `operatorCode`,
  `length`, `deliveredDate`, `errorCode` and `referenceId`.
- Balance queries use `POST balance`; `getAvailablePackages()` now returns `amount` and `name` per package.
- IYS requests send the reference id in the request header and `send()` returns the decoded JSON array.
- `NetGsmChannel::send()` returns the job id and throws `InvalidArgumentException` for invalid messages.
- Typed properties, parameters and return types across the codebase; no implicitly nullable parameters (PHP 8.4).
- Tests rewritten for PHPUnit 11/12 and Orchestra Testbench 10/11.
- GitHub Actions matrix covers PHP 8.4/8.5 with Laravel 12/13, plus a Pint job.
- License copyright holder is Selim Görmüş.

### Fixed

- `NetGsm::getReports()` applied the end date as the start date.
- Turkish translation of `query_limit_exceed` showed the OTP package message.

### Removed

- Laravel 10 and Laravel 11 support.
- PHP 8.2 and PHP 8.3 support.
- Legacy HTTP GET / XML sending (`setSendMethod()`, `getSendMethod()`, `NETGSM_SMS_SENDING_METHOD`).
- `NETGSM_LANGUAGE`; use `NETGSM_ENCODING` instead.
- `setAuthorizedData()` / `isAuthorizedData()`; use `setIysFilter()` instead.
- `NetGsmSmsDetailReport`; use `NetGsmSmsReport` or `NetGsm::getStats()` instead.
- `setVersion()`, `setView()` and `setType()` report filters.
- `ext-simplexml` requirement and StyleCI configuration.

## [4.6.0] - 2024-04-08

- Laravel 11 and PHP 8.3 support added.

## [4.5.0] - 2023-05-11

- PHP 8.2 support added.

## [4.4.0] - 2023-02-16

- Laravel 10 support added.

## [4.3.0] - 2022-02-08

- Laravel 9 support added.

## [4.2.0] - 2021-06-03

- PHP 8 support added.

## [4.1.0] - 2021-06-01

- Bulk address adding support added to IYS.

## [4.0.0] - 2021-04-26

- IYS integration (add, search) added.

## [3.0.1] - 2020-10-08

- Upgrade packages for Laravel 8.
- Migrate phpunit config.

## [3.0.0] - 2020-10-06

- Laravel NetGsm package now compatible with Laravel 8.

## [2.1.1] - 2020-03-05

- `vendor:publish` section in the documentation has been fixed.
- Error code descriptions fixed for balance service.

## [2.1.0] - 2020-03-04

- Added an exception for unhandled NetGsm error codes.
- Laravel 7 support added.

## [2.0.0] - 2020-03-02

- Namespaces and folder structure changed.
- Available credit and packages service added.
- Tests updated.

## [1.0.1] - 2020-02-17

- Default SMS sending method changed to `get`.

## [1.0.0] - 2020-02-17

- Initial release.

[Unreleased]: https://github.com/siberfx/netgsm/compare/5.0.0...HEAD
[5.0.0]: https://github.com/siberfx/netgsm/releases/tag/5.0.0
