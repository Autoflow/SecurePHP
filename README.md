## SecurePHP - error mailer and runtime sensitizing

* predefined error reports
  - error tickets
  - config errors
  - info & notice reports
  - init errors
  - success reports
  - timer alerts
  - transaction & transition errors
  - uncaught exception errors

* send errors to email inboxes
  - send to admin
  - send to users
  - send to cc
  
* treat E_NOTICE's as E_ERROR's (strict mode)
  - catch type conversion errors
  - catch undefined variables, indexes and offsets
  - […]
  
* catch recurring errors
  - inform about repetitives errors (ie. every 30 minutes)
  - optimized for cronjobs

* loose mode
  - handle errors as PHP does

* EOF detection
  - get informed when PHP doesn't reach the end of file

* user defined shutdown function
  - set your own shutdown function

## Usage:

include securephp.php and get a new instance ..
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
