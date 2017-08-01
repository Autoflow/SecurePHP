## Reports simliar to ErrorTickets:

* SuccessTicket
-

## Example usage:

```php

try {

    // start Autoflow\SecurePHP
    $mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance(true, false);

    // display errors
    $mysecure->mute(false);

    // try to access a unset variable
    echo $x;

    }

```

### Result:

```console
/**
* SecurePHP
*
* PhpError
* [27-Jul-2017 16:20:41]
*
* concerned application: C:/Bitnami/apache2/htdocs/GitHub/SecurePHP/2.0/tests/testcase.php
*
* runtime error within C:\Bitnami\apache2\htdocs\GitHub\SecurePHP\2.0\tests\testcase.php, line 14
* description: Undefined variable: e
*
* notes: E_NOTICE
*
* current state: script will be aborted (Strict-Mode)
*
* trace:
* 0 C:\Bitnami\apache2\htdocs\GitHub\SecurePHP\2.0\tests\testcase.php (14) AUTOFLOW\SECUREPHP\PROTECT->error_handler(8, 'Undefined ..', 'C:\Bitnami..', 14, ARRAY)
* {main}
*
*/
```