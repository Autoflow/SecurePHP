# ErrorTicket
-------------
## Example usage:

```php

try
    {

    // start Autoflow\SecurePHP
    $mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance(true, false);

    // display reports
    $mysecure->mute(false)

    // create and raise error ticket
    $ticket = new \ErrorTicket('Database update failed today', 'Retry in 30 minutes');
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
* error ticket in C:\Bitnami\apache2\htdocs\GitHub\SecurePHP\2.0\tests\testcase.php, line 33
* description: Database update failed today
*
* current state: Retry in 30 minutes
*
* trace:
* {main}
*
*/
```

### ErrorTicket
* ErrorTicket::raise(int $timeout) - raise error ticket and send to log, mail inboxes and/or STDOUT/STDERR
* ErrorTicket::send_to(string $receipients) - list of ticket [receipients](receipients.md)
* ErrorTicket::state(string $state) - set state description
* ErrorTicket::note(string $note) - set a note
* ErrorTicket::add_param(string $name, mixed $value) - add a parameter value
* ErrorTicket::add_params(array $params) - add a list of parameters


### Default receipients:
* admin
* log

### Similiar reports:
* [Warning](warning.md)
* [ConfigError](configerror.md)
* [InitError](initerror.md)