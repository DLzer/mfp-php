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

## Installing PHP-MFP

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

## Version Guide
- 0.1.0 Initial Release
- 0.1.1 Fix autoload issue

## Support
Feel free to fork this project, any support is appreciated.