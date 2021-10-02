# MFP-PHP
![GitHub](https://img.shields.io/github/license/dlzer/mfp-php?style=flat-square)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/dlzer/mfp-php?style=flat-square)


MFP-PHP is a PHP library that makes it easy to request a single days macro data from a MyFitnessPal users diary.
- Simple interface allows for quick start up
- Takes only the MyFitnessPal username and a single Date (YYYY-MM-DD)
- Lightweight class, so no worries about bulking down a project.
- No dependencies constraints

## Requirements
- PHP 7.4^
- Composer

## Usage

! The uses MFP profile needs to be set to public for macros to be readable.

```php
use DLzer\MfpService;

$macros = (new MfpService("YourUsername", "2021-10-01"))->fetch();
```

## Installing MFP-PHP

```bash
composer require dlzer/mfp-php
``` 

## Testing

The most recent version of MFP-PHP includes PHPUnit test cases. To run them with composer:
```bash
$ composer test
# Or for coverage analysis
$ composer test:coverage
```

## Version Guide
- 0.1.0 Initial Release
- 0.1.1 Fix autoload issue
- 0.1.2 Added check for Username
- 0.1.3 Added check for date format
- 0.1.4 Cleanup Username Check
- 0.1.5 Added PHPUnit Test Cases
- 0.1.6 Macro Alignment Adjustment
- 0.1.7 Error Handling, Cleanup & Prep for PSR-18 Http-Client
- 2.0.0 Major Version Bump

## Support
Feel free to fork this project, any support is appreciated.