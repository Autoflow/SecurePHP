## PHP error mailer and replacement for error-, exception- and shutdown handler.


* send error messages to email inboxes
  - send to admin
  - send to users
  - send to cc
  
* treat E_NOTICE's as E_ERROR's
  - no more undefined variables
  - no more Array to String conversion 
  - […]


### Usage:
```php
include securephp.php of SecurePHP library.
require_once('vendor/autoflow/securephp/secure.php');
```

### Composer
If you want to install with composer,
```json
{
	"require": {
		"autoflow/securephp": "dev-master"
	},
	"autoload": {
		"files": ["vendor/autoflow/securephp/secure.php"]
	}
}
```

and require autoload.php and execute method.

```php
require('vendor/autoload.php');
AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance();
```
