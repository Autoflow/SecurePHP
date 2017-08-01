## Example usage:

```php

try
    {

    // start Autoflow\SecurePHP
    $mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance(true, false);

    // display reports
    $mysecure->mute(false)

    // create and raise error ticket
    $ticket = new Warning('Database update failed today', 'Retry in 30 minutes');
    $ticket->raise();


```

### Result:

```text
/**
* SecurePHP
*
* ErrorTicket
* [01-Aug-2017 12:24:13]
*
* send by: C:/Bitnami/apache2/htdocs/GitHub/SecurePHP/2.0/tests/testcase.php
*
* warning in C:\Bitnami\apache2\htdocs\GitHub\SecurePHP\2.0\tests\testcase.php, line 33
* description: Database update failed today
*
* current state: Retry in 30 minutes
*
* trace:
* {main}
*
*/
```

### Default receipients:
* admin
* log

### Similiar reports:
* [ErrorTicket](errorticket.md)
* [ConfigError](configerror.md)