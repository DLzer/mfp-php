MFP-PHP, MyFitnessPal Diary Library
===================================

MFP-PHP is a PHP library that makes it easy to request a single days macro data from a MyFitnessPal users diary.
- Simple interface allows for quick start up
- Takes only the MyFitnessPal username and a single Date (YYYY-MM-DD)
- Lightweight class, so no worries about bulking down a project.

## Usage
It really is simple..
```php
## Create a new instance with Username and Date String ( YYYY-MM-DD ), then fetch the macro data.
$mfp = (new \DLzer\MFP\MfpService($username, $date))->fetch();
```

## Installing MFP-PHP

The reccomended method for installing MFP-PHP is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of MFP-PHP:

```bash
composer require dlzer/mfp-php
``` 

After installing, require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

That's it!

## Testing

The most recent version of MFP-PHP includes PHPUnit test cases. To run them with composer:
```bash
./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/MfpTest
```

## Version Guide
- 0.1.0 Initial Release
- 0.1.1 Fix autoload issue
- 0.1.2 Added check for Username
- 0.1.3 Added check for date format
- 0.1.4 Cleanup Username Check
- 0.1.5 Added PHPUnit Test Cases
- 0.1.6 Macro Alignment Adjustment

## Support
Feel free to fork this project, any support is appreciated.