# InitError (extends [ErrorTicket][errorticket.md)]
-----------
## Example usage:

```php

try
    {

    // start Autoflow\SecurePHP
    $mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance(true, false);

    // display reports
    $mysecure->mute(false)

    // some init errors occured
    throw new Exception('some init error occured ..');

    }

catch(AUTOFLOW\SECUREPHP\E_INIT  $e)
    {

       // create and raise error ticket
       $ticket = new InitError('could not load SecurePHP library', 'script execution failed due to an error', $e);
       $ticket->raise();

    }

```

### Result:

```text
/**
* SecurePHP
*
* InitError
* [01-Aug-2017 14:12:21]
*
* send by: C:/Bitnami/apache2/htdocs/GitHub/SecurePHP/2.0/tests/testcase.php
*
* description: could not load SecurePHP library
*
* current state: script execution failed due to an error

* previously:
*
* Exception in C:\Bitnami\apache2\htdocs\GitHub\SecurePHP\2.0\tests\testcase.php, line 24
* description: some init error occured ...
*
* trace:
* {main}
*
*/
```

### Default receipients:
* admin > user
* log

### For more see ..
* [ErrorTicket](errorticket.md)

### Similiar reports:
* [Warning](warning.md)
* [ConfigError](configerror.md)