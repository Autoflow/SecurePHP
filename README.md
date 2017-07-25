## PHP error mailer and replacement for error-, exception- and shutdown handler.


* send error messages to email inboxes
  - send to admin
  - send to users
  - send to cc
  
* treat E_NOTICE's as E_ERROR's (strict mode)
  - catch type conversion errors
  - catch undefined variables, indexes and offsets
  - […]
  
* loose mode

* strict mode


### Usage:
include securephp.php of SecurePHP library.
```php
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

and require autoload.php and getInstance() method.

```php
require('vendor/autoload.php');
AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance();
```
