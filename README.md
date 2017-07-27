## SecurePHP - error mailer and runtime sensitizing

### Delivers:

* predefined error reports based on exceptions
  - [error tickets](doc/errorticket.md)
  - [config errors](doc/configerror.md)
  - [batch reports](doc/batchreport.md)
  - [info & notice reports](doc/inforeports.md)
  - init error reports
  - success reports
  - timer alerts
  - transaction & transition reports
  - uncaught exception reports
  - [..]
  - easily sent by email, to STDERR or to log
  

* [send errors to email inboxes](doc/email_basisc.md)
  - send to admin
  - send to users
  - send to cc
  
* treat E_NOTICE's as E_ERROR's (strict mode)
  - catch type conversion errors
  - catch undefined variables, indexes and offsets
  - […]
  
* [catch recurring errors](doc/timeout.md)
  - inform about repetitives errors (ie. every 30 minutes)
  - optimized for cronjobs

* loose mode
  - handle errors as PHP does

* EOF detection
  - get informed when PHP doesn't reach the end of file

* user defined shutdown function
  - set your own shutdown function

## Usage

include secure.php in your project and get a fresh instance ..
```php
require_once('vendor/autoflow/securephp/secure.php');
$mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance();
```

### Composer

If you want to install with composer,
```json
{
"require": 
  {
  "autoflow/securephp": "^2.0.0"
  }
}
```

and require autoload.php and get a new instance ..

```php
require('vendor/autoload.php');
$mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance();
```
