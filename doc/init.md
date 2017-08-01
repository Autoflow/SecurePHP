### Settings you can specify

```php
AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance()->init( string|bool $logfile, bool $stderr, int $devices);
```

### Parameters

* logfile
    - 'true' if you want an auto created log file
    - 'false' if you want no logging
    - or path to your own logfile

* stderr
    - log to STDERR when using in CLI

* devices
     - SAPI for allowing in web mode only
     - CLI for allowing in CLI mode only
     - ALL for allowing in all devices

### Returns

Singleton instance of AUTOFLOW\SECUREPHP\BOOTSTRAP::instance