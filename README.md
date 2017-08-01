## SecurePHP - error mailer and runtime sensitizing

### Provides:

* [secure default mode](doc/default.md)

* predefined reports based on exceptions
    - [error tickets](doc/errorticket.md)
    - [config errors](doc/configerror.md)
    - [batch reports](doc/batchreport.md)
    - [info & notice reports](doc/inforeports.md)
    - [warning report](doc/warningreports.md)
    - [init error reports](doc/initerror.md)
    - [success reports](doc/successreport.md)
    - [timer alerts & reminders](doc/timeout.md)
    - [transaction & transition reports](doc/execution_errors.md)
    - [uncaught exception reports](doc/uncaught.md)
    - [..]
    - easily sent by [email](doc/email_basisc.md), to STDERR or to log
  

* [send reports to email inboxes](doc/email_basisc.md)
    - send to admin
    - send to users
    - send to cc
  
* treat E_NOTICE's as E_ERROR's (strict mode)
    - catch type conversion errors
    - [catch undefined variables, indexes and offsets](doc/e_notice.md)
    - […]
  
* catch recurring errors
  - [inform about repetitives errors](doc/timeout.md) (ie. every 30 minutes)
  - optimized for cronjobs

* loose mode
    - [handle errors as PHP does](doc/loose.md)

* EOF detection
    - [get informed when PHP doesn't reach the end of file](doc/eof.md)

* user defined shutdown function
    - [set your own shutdown function](doc/shutdown_function.md)

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
